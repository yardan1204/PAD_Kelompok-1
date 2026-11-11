/* =============================================================================
   campus-space.js — Interaksi ringan form (FE-05)

   Dimuat di dua mode (sama seperti campus-space.css):
   - Mode Vite : di-import oleh resources/js/app.js.
   - Mode CDN  : disisipkan langsung oleh layouts/app.blade.php.
   Karena itu file ini berupa script biasa (tanpa import/export) dan aman bila
   dimuat dua kali.

   Prinsip: progressive enhancement. Tanpa JavaScript, form tetap terkirim dan
   divalidasi oleh atribut HTML5 + FormRequest di server. Script ini hanya:
   - menampilkan pesan validasi berbahasa Indonesia di bawah field (bukan bubble browser),
     lengkap dengan aria-invalid & aria-describedby;
   - memeriksa waktu selesai > waktu mulai          (atribut data-after="id_waktu_mulai");
   - memeriksa peserta <= kapasitas ruang terpilih   (atribut data-capacity-from="id_select_ruang",
                                                      option ruang membawa data-capacity);
   - menolak isian yang hanya berisi spasi            (atribut data-not-blank);
   - merapikan spasi di awal/akhir token QR           (atribut data-trim);
   - memfokuskan field pertama yang ditandai galat oleh server.
   FE-06:
   - konfirmasi sebelum hapus/batal                   (atribut data-confirm="pesan" pada <form>);
   - tombol cetak laporan → window.print()             (atribut data-print, tombol disembunyikan tanpa JS);
   - pembaruan otomatis dashboard yang bisa dijeda     (atribut data-auto-refresh="detik"); refresh ditunda
     saat pengguna sedang mengisi form, tab tidak terlihat, atau dialog cetak terbuka.
     Tanpa JS, <noscript><meta http-equiv="refresh"></noscript> di halaman tetap berlaku.
   PROMPT 2 (layout sidebar):
   - sidebar drawer di layar < 1024px                  ([data-drawer], [data-drawer-open], [data-drawer-close],
                                                      [data-drawer-overlay]; Esc menutup, fokus terkunci);
   - tombol tutup pesan flash                          ([data-dismissible] berisi [data-dismiss]);
   - konfirmasi data-confirm lewat <dialog id="cs-confirm-dialog"> (bukan confirm() bawaan),
     dialog berform sendiri lewat [data-dialog-open="id"] / [data-dialog-close];
   - pratinjau foto ruang sebelum diunggah            (input[data-photo-input] -> img[data-photo-preview]);
   - status loading tombol submit form POST            ("Menyimpan...", nonaktif; teks lain lewat data-loading-text).
   Script ini TIDAK mengubah status booking apa pun; semua transaksi tetap lewat server.
   ============================================================================= */
(function () {
    'use strict';

    if (window.__campusSpaceInit) {
        return;
    }
    window.__campusSpaceInit = true;

    var MESSAGES = {
        valueMissing: function () { return 'Isian ini wajib diisi.'; },
        typeMismatch: function () { return 'Format isian tidak sesuai.'; },
        badInput: function () { return 'Masukkan nilai yang valid.'; },
        patternMismatch: function (el) { return el.dataset.patternMessage || 'Format isian tidak sesuai.'; },
        tooLong: function (el) { return 'Maksimal ' + el.maxLength + ' karakter.'; },
        tooShort: function (el) { return 'Minimal ' + el.minLength + ' karakter.'; },
        rangeUnderflow: function (el) { return 'Nilai minimal ' + el.min + '.'; },
        rangeOverflow: function (el) { return 'Nilai maksimal ' + el.max + '.'; },
        stepMismatch: function () { return 'Masukkan bilangan bulat.'; }
    };

    function messageFor(el) {
        if (el.validity.customError) {
            return el.validationMessage;
        }
        for (var key in MESSAGES) {
            if (el.validity[key]) {
                return MESSAGES[key](el);
            }
        }
        return el.validationMessage;
    }

    function setDescribedBy(el, id, add) {
        var ids = (el.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean);
        ids = ids.filter(function (item) { return item !== id; });
        if (add) {
            ids.push(id);
        }
        if (ids.length) {
            el.setAttribute('aria-describedby', ids.join(' '));
        } else {
            el.removeAttribute('aria-describedby');
        }
    }

    // Pesan galat dari server (<x-field-error>) disembunyikan begitu pengguna mengubah isian,
    // supaya tidak ada dua pesan untuk satu field.
    function hideServerError(el) {
        var serverError = el.id ? document.getElementById(el.id + '-error') : null;
        if (serverError && !serverError.hidden) {
            serverError.hidden = true;
            setDescribedBy(el, serverError.id, false);
            el.removeAttribute('aria-invalid');
        }
    }

    function showError(el) {
        if (!el.id) {
            return;
        }
        hideServerError(el);
        var id = el.id + '-client-error';
        var node = document.getElementById(id);
        if (!node) {
            node = document.createElement('p');
            node.id = id;
            node.className = 'cs-form-error';
            // Urutan konsisten: input → hint → pesan galat.
            var anchor = document.getElementById(el.id + '-error')
                || document.getElementById(el.id + '-hint')
                || document.getElementById(el.id + '-capacity')
                || el;
            anchor.insertAdjacentElement('afterend', node);
        }
        node.textContent = messageFor(el);
        node.hidden = false;
        el.setAttribute('aria-invalid', 'true');
        setDescribedBy(el, id, true);
    }

    function clearError(el) {
        if (!el.id) {
            return;
        }
        var node = document.getElementById(el.id + '-client-error');
        if (node) {
            node.hidden = true;
            node.textContent = '';
        }
        setDescribedBy(el, el.id + '-client-error', false);
        if (!document.getElementById(el.id + '-error') || document.getElementById(el.id + '-error').hidden) {
            el.removeAttribute('aria-invalid');
        }
    }

    function syncRules(form) {
        // Waktu selesai harus setelah waktu mulai (format datetime-local bisa dibandingkan sebagai teks).
        form.querySelectorAll('[data-after]').forEach(function (end) {
            var start = document.getElementById(end.dataset.after);
            if (!start) {
                return;
            }
            end.min = start.value || '';
            var invalid = start.value && end.value && end.value <= start.value;
            end.setCustomValidity(invalid ? 'Waktu selesai harus setelah waktu mulai.' : '');
        });

        // Jumlah peserta tidak boleh melebihi kapasitas ruang terpilih.
        form.querySelectorAll('[data-capacity-from]').forEach(function (input) {
            var select = document.getElementById(input.dataset.capacityFrom);
            var option = select && select.selectedOptions ? select.selectedOptions[0] : null;
            var capacity = option ? Number(option.dataset.capacity || 0) : 0;
            var info = document.getElementById(input.id + '-capacity');
            if (capacity > 0) {
                input.max = String(capacity);
                if (info) {
                    info.textContent = 'Kapasitas ruang terpilih: ' + capacity + ' orang.';
                }
            } else {
                input.removeAttribute('max');
                if (info) {
                    info.textContent = info.dataset.default || '';
                }
            }
            var over = capacity > 0 && input.value !== '' && Number(input.value) > capacity;
            input.setCustomValidity(over ? 'Jumlah peserta melebihi kapasitas ruang (' + capacity + ' orang).' : '');
        });

        // Isian tidak boleh hanya berisi spasi (textarea tidak mendukung atribut pattern).
        form.querySelectorAll('[data-not-blank]').forEach(function (el) {
            var blank = el.value !== '' && el.value.trim() === '';
            el.setCustomValidity(blank ? 'Isian tidak boleh hanya berisi spasi.' : '');
        });
    }

    function revalidate(form) {
        form.querySelectorAll('input, select, textarea').forEach(function (el) {
            if (!el.willValidate || el.dataset.touched !== '1') {
                return;
            }
            if (el.validity.valid) {
                clearError(el);
            } else {
                showError(el);
            }
        });
    }

    function enhanceForm(form) {
        var focusPending = false;

        syncRules(form);

        form.addEventListener('input', function (event) {
            hideServerError(event.target);
            syncRules(form);
            revalidate(form);
        });

        form.addEventListener('change', function (event) {
            var el = event.target;
            if (el.matches('[data-trim]') && el.value !== el.value.trim()) {
                el.value = el.value.trim();
            }
            if (el.value !== '') {
                el.dataset.touched = '1';
            }
            syncRules(form);
            revalidate(form);
        });

        // Saat submit diblokir validasi, browser memicu event "invalid" (tidak bubble -> pakai capture).
        // Bubble bawaan browser diganti pesan di bawah field; field pertama yang salah difokuskan.
        form.addEventListener('invalid', function (event) {
            var el = event.target;
            event.preventDefault();
            el.dataset.touched = '1';
            showError(el);
            if (!focusPending) {
                focusPending = true;
                el.focus();
                window.setTimeout(function () { focusPending = false; }, 0);
            }
        }, true);
    }

    // Konfirmasi sebelum aksi destruktif. Server tetap wajib memvalidasi aksi ini.
    // Konfirmasi <form data-confirm="pesan"> memakai <dialog id="cs-confirm-dialog"> (x-confirm-dialog di layout).
    // Browser tanpa dukungan <dialog> jatuh ke window.confirm.
    function initConfirm() {
        var dialog = document.getElementById('cs-confirm-dialog');
        var supportsDialog = dialog && typeof dialog.showModal === 'function';
        var pendingForm = null;

        if (supportsDialog) {
            dialog.addEventListener('close', function () {
                var form = pendingForm;
                pendingForm = null;
                if (form && dialog.returnValue === 'confirm') {
                    form.dataset.confirmed = 'true';
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                }
            });
        }

        document.addEventListener('submit', function (event) {
            var form = event.target;
            if (!form.matches('form[data-confirm]')) {
                return;
            }
            if (form.dataset.confirmed === 'true') {
                delete form.dataset.confirmed;
                return;
            }
            event.preventDefault();

            if (!supportsDialog) {
                if (window.confirm(form.dataset.confirm)) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
                return;
            }

            pendingForm = form;
            dialog.querySelector('[data-confirm-message]').textContent = form.dataset.confirm;
            dialog.returnValue = '';
            dialog.showModal();
        });
    }

    // Dialog dengan form sendiri (mis. Tolak + alasan): [data-dialog-open="id"] membuka, [data-dialog-close] menutup.
    function initDialogs() {
        document.querySelectorAll('[data-dialog-open]').forEach(function (button) {
            var dialog = document.getElementById(button.getAttribute('data-dialog-open'));
            if (!dialog || typeof dialog.showModal !== 'function') {
                return;
            }
            button.hidden = false;
            button.addEventListener('click', function () {
                dialog.showModal();
                var field = dialog.querySelector('textarea, input:not([type="hidden"]), select');
                if (field) {
                    field.focus();
                }
            });
        });
        document.querySelectorAll('dialog [data-dialog-close]').forEach(function (button) {
            button.addEventListener('click', function () {
                button.closest('dialog').close();
            });
        });
    }

    function initPrint() {
        document.querySelectorAll('[data-print]').forEach(function (button) {
            button.hidden = false;
            button.addEventListener('click', function () { window.print(); });
        });
    }

    function initAutoRefresh() {
        var root = document.querySelector('[data-auto-refresh]');
        if (!root) {
            return;
        }
        var seconds = Number(root.dataset.autoRefresh) || 30;
        var paused = false;
        var printing = false;
        var dirty = false;
        var status = document.querySelector('[data-refresh-status]');
        var toggle = document.querySelector('[data-refresh-toggle]');

        document.addEventListener('input', function (event) {
            if (event.target.closest('form')) {
                dirty = true;
            }
        });
        window.addEventListener('beforeprint', function () { printing = true; });
        window.addEventListener('afterprint', function () { printing = false; });

        function render() {
            if (status) {
                status.textContent = paused
                    ? 'Pembaruan otomatis dijeda'
                    : 'Live · diperbarui otomatis tiap ' + seconds + ' detik';
            }
            if (toggle) {
                toggle.hidden = false;
                toggle.setAttribute('aria-pressed', paused ? 'true' : 'false');
                toggle.textContent = paused ? 'Lanjutkan pembaruan' : 'Jeda pembaruan';
            }
        }

        function busy() {
            var active = document.activeElement;
            var editing = active && active.closest && active.closest('form') && active.matches('input, select, textarea');
            return paused || printing || dirty || document.hidden || editing;
        }

        function tick() {
            if (busy()) {
                window.setTimeout(tick, seconds * 1000);
                return;
            }
            window.location.reload();
        }

        if (toggle) {
            toggle.addEventListener('click', function () {
                paused = !paused;
                render();
            });
        }
        render();
        window.setTimeout(tick, seconds * 1000);
    }

    // Sidebar sebagai drawer di layar < 1024px: tombol [data-drawer-open] membuka, overlay / Esc /
    // [data-drawer-close] menutup, fokus Tab terkunci di dalam drawer, fokus kembali ke tombol pembuka.
    function initDrawer() {
        var drawer = document.querySelector('[data-drawer]');
        var openButton = document.querySelector('[data-drawer-open]');
        var overlay = document.querySelector('[data-drawer-overlay]');
        if (!drawer || !openButton || !overlay) {
            return;
        }

        var desktop = window.matchMedia('(min-width: 64rem)');
        var focusable = 'a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])';

        function setOpen(open) {
            drawer.setAttribute('data-open', open ? 'true' : 'false');
            overlay.hidden = !open;
            openButton.setAttribute('aria-expanded', open ? 'true' : 'false');
            document.body.style.overflow = open ? 'hidden' : '';
            if (open) {
                var first = drawer.querySelector(focusable);
                if (first) {
                    first.focus();
                }
            } else {
                openButton.focus();
            }
        }

        function isOpen() {
            return drawer.getAttribute('data-open') === 'true';
        }

        openButton.addEventListener('click', function () { setOpen(true); });
        overlay.addEventListener('click', function () { setOpen(false); });
        drawer.querySelectorAll('[data-drawer-close]').forEach(function (button) {
            button.addEventListener('click', function () { setOpen(false); });
        });

        document.addEventListener('keydown', function (event) {
            if (!isOpen() || desktop.matches) {
                return;
            }
            if (event.key === 'Escape') {
                setOpen(false);
                return;
            }
            if (event.key === 'Tab') {
                var items = Array.prototype.filter.call(drawer.querySelectorAll(focusable), function (el) {
                    return el.offsetParent !== null;
                });
                if (!items.length) {
                    return;
                }
                var first = items[0];
                var last = items[items.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        });

        // Saat layar dilebarkan ke desktop, tutup state drawer agar tidak mengunci scroll.
        desktop.addEventListener('change', function (event) {
            if (event.matches && isOpen()) {
                drawer.setAttribute('data-open', 'false');
                overlay.hidden = true;
                openButton.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
        });
    }

    // Tombol × pada pesan flash ([data-dismiss] di dalam [data-dismissible]).
    function initDismiss() {
        document.querySelectorAll('[data-dismissible] [data-dismiss]').forEach(function (button) {
            button.hidden = false;
            button.addEventListener('click', function () {
                var box = button.closest('[data-dismissible]');
                if (box) {
                    box.remove();
                }
            });
        });
    }

    // Pratinjau foto sebelum diunggah: input[data-photo-input] -> img[data-photo-preview] di form yang sama.
    function initPhotoPreview() {
        document.querySelectorAll('input[data-photo-input]').forEach(function (input) {
            var preview = input.form && input.form.querySelector('img[data-photo-preview]');
            if (!preview || !window.URL) {
                return;
            }
            input.addEventListener('change', function () {
                var file = input.files && input.files[0];
                if (!file || file.type.indexOf('image/') !== 0) {
                    return;
                }
                preview.src = URL.createObjectURL(file);
                preview.alt = 'Pratinjau ' + file.name;
                preview.hidden = false;
            });
        });
    }

    // Cegah kirim ganda: tombol submit form POST dinonaktifkan + teks "Menyimpan..." setelah submit benar-benar
    // berjalan (dicek setelah validasi klien & dialog konfirmasi sempat membatalkannya). Form GET (filter) tidak diubah.
    function initSubmitLoading() {
        document.addEventListener('submit', function (event) {
            var form = event.target;
            if ((form.getAttribute('method') || 'get').toLowerCase() !== 'post') {
                return;
            }
            var button = event.submitter || form.querySelector('[type="submit"]');
            window.setTimeout(function () {
                if (event.defaultPrevented || !button || button.disabled) {
                    return;
                }
                button.dataset.originalHtml = button.innerHTML;
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
                button.textContent = button.dataset.loadingText || 'Menyimpan...';
            }, 0);
        });

        // Kembali lewat tombol Back (bfcache): pulihkan tombol agar bisa dipakai lagi.
        window.addEventListener('pageshow', function (event) {
            if (!event.persisted) {
                return;
            }
            document.querySelectorAll('button[aria-busy="true"]').forEach(function (button) {
                button.disabled = false;
                button.removeAttribute('aria-busy');
                if (button.dataset.originalHtml) {
                    button.innerHTML = button.dataset.originalHtml;
                }
            });
        });
    }

    // Deteksi koneksi jaringan lambat & kontrol affordance skeleton loading
    function initNetworkAwareSkeleton() {
        var conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;

        function isSlowNetwork() {
            if (!conn) {
                return false;
            }
            var isSlowType = conn.effectiveType === 'slow-2g' || conn.effectiveType === '2g' || conn.effectiveType === '3g';
            var isLowBandwidth = typeof conn.downlink === 'number' && conn.downlink < 1.5;
            var isHighLatency = typeof conn.rtt === 'number' && conn.rtt > 500;
            var isSaveData = Boolean(conn.saveData);

            return isSlowType || isLowBandwidth || isHighLatency || isSaveData;
        }

        function updateNetworkState() {
            var slow = isSlowNetwork();
            if (slow) {
                document.documentElement.classList.add('cs-slow-network');
                document.querySelectorAll('[data-skeleton-container]').forEach(function (el) {
                    el.classList.add('is-skeleton-active');
                });
            } else {
                document.documentElement.classList.remove('cs-slow-network');
                document.querySelectorAll('[data-skeleton-container]').forEach(function (el) {
                    el.classList.remove('is-skeleton-active');
                });
            }
        }

        if (conn) {
            updateNetworkState();
            conn.addEventListener('change', updateNetworkState);
        }
    }

    // SPA-Like Navigation with Contextual Skeleton Transitions
    function initSpaNavigation() {
        if (window.__csSpaInit) {
            return;
        }
        window.__csSpaInit = true;

        function getSkeletonHtml(url) {
            var path = '';
            try {
                path = new URL(url, window.location.origin).pathname;
            } catch (e) {
                path = url;
            }

            // Halaman Jelajahi / Kelola Ruangan
            if (path.indexOf('/rooms') !== -1 && path.indexOf('/create') === -1 && path.indexOf('/edit') === -1) {
                return '<div class="cs-skeleton-page" role="status" aria-busy="true" aria-live="polite">' +
                    '<span class="sr-only">Memuat data ruangan dari database...</span>' +
                    '<div class="flex flex-col gap-2">' +
                        '<div class="cs-skeleton cs-skeleton-title" style="width: 240px; height: 2rem;"></div>' +
                        '<div class="cs-skeleton cs-skeleton-text" style="width: 380px;"></div>' +
                    '</div>' +
                    '<div class="cs-skeleton-card flex items-center justify-between gap-4 p-4">' +
                        '<div class="cs-skeleton cs-skeleton-btn" style="width: 160px;"></div>' +
                        '<div class="flex gap-2">' +
                            '<div class="cs-skeleton cs-skeleton-badge"></div>' +
                            '<div class="cs-skeleton cs-skeleton-badge"></div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="cs-skeleton-grid">' +
                        [1, 2, 3, 4, 5, 6].map(function () {
                            return '<div class="cs-skeleton-card flex flex-col gap-3">' +
                                '<div class="cs-skeleton" style="height: 180px; width: 100%; border-radius: 0.5rem;"></div>' +
                                '<div class="flex items-center justify-between mt-2">' +
                                    '<div class="cs-skeleton cs-skeleton-title" style="width: 60%; height: 1.25rem;"></div>' +
                                    '<div class="cs-skeleton cs-skeleton-badge"></div>' +
                                '</div>' +
                                '<div class="cs-skeleton cs-skeleton-text" style="width: 80%;"></div>' +
                                '<div class="cs-skeleton cs-skeleton-text-sm" style="width: 50%;"></div>' +
                                '<div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-100">' +
                                    '<div class="cs-skeleton cs-skeleton-text-sm" style="width: 70px;"></div>' +
                                    '<div class="cs-skeleton cs-skeleton-btn" style="width: 90px; height: 2rem;"></div>' +
                                '</div>' +
                            '</div>';
                        }).join('') +
                    '</div>' +
                '</div>';
            }

            // Halaman Dashboard
            if (path === '/dashboard' || path === '/') {
                return '<div class="cs-skeleton-page" role="status" aria-busy="true" aria-live="polite">' +
                    '<span class="sr-only">Memuat data dashboard dari database...</span>' +
                    '<div class="flex flex-col gap-2">' +
                        '<div class="cs-skeleton cs-skeleton-title" style="width: 260px; height: 2rem;"></div>' +
                        '<div class="cs-skeleton cs-skeleton-text" style="width: 420px;"></div>' +
                    '</div>' +
                    '<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">' +
                        [1, 2, 3, 4].map(function () {
                            return '<div class="cs-skeleton-card p-5 relative">' +
                                '<div class="cs-skeleton cs-skeleton-text-sm" style="width: 50%;"></div>' +
                                '<div class="cs-skeleton cs-skeleton-title mt-3" style="width: 35%; height: 2.25rem;"></div>' +
                                '<div class="cs-skeleton cs-skeleton-avatar absolute top-5 right-5" style="width: 2.5rem; height: 2.5rem;"></div>' +
                            '</div>';
                        }).join('') +
                    '</div>' +
                    '<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">' +
                        '<div class="lg:col-span-2 cs-skeleton-table-wrapper">' +
                            '<div class="flex justify-between items-center mb-4">' +
                                '<div class="cs-skeleton cs-skeleton-title" style="width: 180px; height: 1.25rem;"></div>' +
                                '<div class="cs-skeleton cs-skeleton-badge"></div>' +
                            '</div>' +
                            [1, 2, 3, 4].map(function () {
                                return '<div class="cs-skeleton-table-row">' +
                                    '<div class="cs-skeleton cs-skeleton-avatar" style="width: 2rem; height: 2rem;"></div>' +
                                    '<div class="flex-1 flex flex-col gap-1">' +
                                        '<div class="cs-skeleton cs-skeleton-text" style="width: 70%;"></div>' +
                                        '<div class="cs-skeleton cs-skeleton-text-sm" style="width: 40%;"></div>' +
                                    '</div>' +
                                    '<div class="cs-skeleton cs-skeleton-badge"></div>' +
                                '</div>';
                            }).join('') +
                        '</div>' +
                        '<div class="cs-skeleton-table-wrapper">' +
                            '<div class="cs-skeleton cs-skeleton-title mb-4" style="width: 140px; height: 1.25rem;"></div>' +
                            '<div class="cs-skeleton" style="height: 140px; width: 100%; border-radius: 0.5rem; margin-bottom: 1rem;"></div>' +
                            '<div class="cs-skeleton cs-skeleton-text" style="width: 90%;"></div>' +
                            '<div class="cs-skeleton cs-skeleton-text" style="width: 75%;"></div>' +
                        '</div>' +
                    '</div>' +
                '</div>';
            }

            // Halaman Jadwal, Peminjaman, Utilisasi, Pengguna, Unit (Tabel / Rekap)
            return '<div class="cs-skeleton-page" role="status" aria-busy="true" aria-live="polite">' +
                '<span class="sr-only">Memuat data live dari database...</span>' +
                '<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">' +
                    '<div class="flex flex-col gap-2">' +
                        '<div class="cs-skeleton cs-skeleton-title" style="width: 220px; height: 2rem;"></div>' +
                        '<div class="cs-skeleton cs-skeleton-text" style="width: 360px;"></div>' +
                    '</div>' +
                    '<div class="flex gap-2">' +
                        '<div class="cs-skeleton cs-skeleton-btn" style="width: 120px;"></div>' +
                    '</div>' +
                '</div>' +
                '<div class="cs-skeleton-card flex items-center justify-between gap-4 p-4">' +
                    '<div class="cs-skeleton cs-skeleton-text" style="width: 250px; height: 1.5rem;"></div>' +
                    '<div class="flex gap-2">' +
                        '<div class="cs-skeleton cs-skeleton-badge" style="width: 80px;"></div>' +
                        '<div class="cs-skeleton cs-skeleton-badge" style="width: 80px;"></div>' +
                    '</div>' +
                '</div>' +
                '<div class="cs-skeleton-table-wrapper">' +
                    '<div class="cs-skeleton-table-row pb-3 border-b border-slate-200">' +
                        '<div class="cs-skeleton cs-skeleton-text" style="width: 25%;"></div>' +
                        '<div class="cs-skeleton cs-skeleton-text" style="width: 25%;"></div>' +
                        '<div class="cs-skeleton cs-skeleton-text" style="width: 20%;"></div>' +
                        '<div class="cs-skeleton cs-skeleton-text" style="width: 15%;"></div>' +
                        '<div class="cs-skeleton cs-skeleton-text" style="width: 15%;"></div>' +
                    '</div>' +
                    [1, 2, 3, 4, 5, 6].map(function () {
                        return '<div class="cs-skeleton-table-row">' +
                            '<div class="cs-skeleton cs-skeleton-text" style="width: 25%;"></div>' +
                            '<div class="cs-skeleton cs-skeleton-text" style="width: 25%;"></div>' +
                            '<div class="cs-skeleton cs-skeleton-badge" style="width: 20%;"></div>' +
                            '<div class="cs-skeleton cs-skeleton-text-sm" style="width: 15%;"></div>' +
                            '<div class="cs-skeleton cs-skeleton-btn" style="width: 15%; height: 2rem;"></div>' +
                        '</div>';
                    }).join('') +
                '</div>' +
            '</div>';
        }

        function updateActiveSidebar(targetUrl) {
            var currentPath = new URL(targetUrl, window.location.origin).pathname;
            document.querySelectorAll('.cs-sidebar-link').forEach(function (link) {
                try {
                    var linkPath = new URL(link.href, window.location.origin).pathname;
                    if (linkPath === currentPath || (currentPath.indexOf(linkPath) === 0 && linkPath !== '/')) {
                        link.setAttribute('aria-current', 'page');
                    } else {
                        link.removeAttribute('aria-current');
                    }
                } catch (e) {}
            });
        }

        function reinitializeUi() {
            initSubmitLoading();
            initPhotoPreview();
            initConfirm();
            initPrint();
            initAutoRefresh();
            initDrawer();
            initDismiss();
            initDialogs();
            document.querySelectorAll('form[data-cs-validate]').forEach(enhanceForm);
        }

        var currentAbortController = null;

        function navigateTo(url, pushState) {
            var mainContent = document.getElementById('main-content');
            if (!mainContent) {
                window.location.href = url;
                return;
            }

            if (currentAbortController) {
                currentAbortController.abort();
            }
            currentAbortController = new AbortController();

            // Beri feedback visual langsung pada sidebar
            updateActiveSidebar(url);

            // Pasang tampilan kerangka Skeleton secara instan
            var skeletonHtml = getSkeletonHtml(url);
            var applySkeleton = function () {
                mainContent.innerHTML = skeletonHtml;
            };

            if (document.startViewTransition && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                document.startViewTransition(applySkeleton);
            } else {
                applySkeleton();
            }

            if (pushState !== false) {
                window.history.pushState({ url: url }, '', url);
            }

            // Tutup drawer ponsel bila sedang terbuka
            var sidebar = document.getElementById('cs-sidebar');
            var overlay = document.querySelector('[data-drawer-overlay]');
            if (sidebar && sidebar.classList.contains('is-open')) {
                sidebar.classList.remove('is-open');
                if (overlay) {
                    overlay.hidden = true;
                }
            }

            window.scrollTo({ top: 0, behavior: 'instant' });

            // Ambil live data halaman baru di background
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Campus-Space-SPA': '1'
                },
                signal: currentAbortController.signal
            })
            .then(function (res) {
                if (!res.ok) {
                    window.location.href = url;
                    return null;
                }
                return res.text();
            })
            .then(function (html) {
                if (!html) {
                    return;
                }
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');

                var newTitle = doc.querySelector('title');
                if (newTitle) {
                    document.title = newTitle.innerText;
                }

                var newMain = doc.getElementById('main-content');
                if (!newMain) {
                    window.location.href = url;
                    return;
                }

                var applyContent = function () {
                    mainContent.innerHTML = newMain.innerHTML;
                };

                if (document.startViewTransition && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    var transition = document.startViewTransition(applyContent);
                    transition.finished.finally(function () {
                        mainContent.focus();
                    });
                } else {
                    applyContent();
                    mainContent.focus();
                }

                reinitializeUi();
            })
            .catch(function (err) {
                if (err.name === 'AbortError') {
                    return;
                }
                window.location.href = url;
            });
        }

        // Tangkap klik tautan navigasi internal
        document.addEventListener('click', function (e) {
            var anchor = e.target.closest('a');
            if (!anchor) {
                return;
            }

            // Lewati klik dengan modifikasi keyboard
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) {
                return;
            }

            if (anchor.target && anchor.target !== '_self') {
                return;
            }

            if (anchor.hasAttribute('download') || anchor.href.indexOf('mailto:') === 0 || anchor.href.indexOf('tel:') === 0) {
                return;
            }

            var currentUrl = new URL(window.location.href);
            var targetUrl;
            try {
                targetUrl = new URL(anchor.href, window.location.origin);
            } catch (err) {
                return;
            }

            if (targetUrl.origin !== currentUrl.origin) {
                return;
            }

            if (targetUrl.pathname === currentUrl.pathname && targetUrl.search === currentUrl.search && targetUrl.hash) {
                return;
            }

            if (targetUrl.pathname.indexOf('/logout') !== -1) {
                return;
            }

            e.preventDefault();
            navigateTo(anchor.href, true);
        });

        window.addEventListener('popstate', function () {
            navigateTo(window.location.href, false);
        });
    }

    function init() {
        initNetworkAwareSkeleton();
        initSpaNavigation();
        initSubmitLoading();
        initPhotoPreview();
        initConfirm();
        initPrint();
        initAutoRefresh();
        initDrawer();
        initDismiss();
        initDialogs();
        document.querySelectorAll('form[data-cs-validate]').forEach(enhanceForm);

        // Fokus ke field pertama yang ditandai galat oleh server setelah redirect.
        var firstInvalid = document.querySelector('form [aria-invalid="true"]');
        if (firstInvalid) {
            firstInvalid.focus();
        }

        // <details data-focus-on-open>: fokus ke isian pertama saat panel dibuka (form Reject).
        document.querySelectorAll('details[data-focus-on-open]').forEach(function (details) {
            details.addEventListener('toggle', function () {
                if (details.open) {
                    // Lewati input hidden (_token dari @csrf) yang tidak bisa difokus.
                    var field = details.querySelector('textarea, select, input:not([type="hidden"])');
                    if (field) {
                        field.focus();
                    }
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
