(function () {
    'use strict';

    var side = document.getElementById('side');
    var btn = document.querySelector('[data-menu-btn]');
    var veil = document.querySelector('[data-veil]');
    function setMenu(open) {
        side.classList.toggle('is-open', open);
        veil.hidden = !open;
        btn.setAttribute('aria-expanded', String(open));
    }
    if (btn) {
        btn.addEventListener('click', function () { setMenu(!side.classList.contains('is-open')); });
        veil.addEventListener('click', function () { setMenu(false); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setMenu(false); });
    }

    document.querySelectorAll('[data-eye]').forEach(function (b) {
        b.addEventListener('click', function () {
            var input = document.getElementById(b.dataset.eye);
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            b.textContent = show ? 'Скрыть' : 'Показать';
            b.setAttribute('aria-label', show ? 'Скрыть пароль' : 'Показать пароль');
        });
    });

    document.querySelectorAll('[data-mask="phone"]').forEach(function (input) {
        input.addEventListener('input', function () {
            var d = input.value.replace(/\D/g, '');
            if (d && d[0] !== '8') d = '8' + d.replace(/^7/, '');
            d = d.slice(0, 11);
            var out = d.slice(0, 1);
            if (d.length > 1) out += '(' + d.slice(1, 4);
            if (d.length >= 4) out += ')';
            if (d.length > 4) out += d.slice(4, 7);
            if (d.length > 7) out += '-' + d.slice(7, 9);
            if (d.length > 9) out += '-' + d.slice(9, 11);
            input.value = out;
        });
    });

    var rules = {
        required: function (v) { return v ? '' : 'Заполните поле'; },
        login: function (v) { return !v ? 'Введите логин' : (/^[A-Za-z0-9]{6,}$/.test(v) ? '' : 'Только латиница и цифры, не менее 6 символов'); },
        password: function (v) { return !v ? 'Введите пароль' : (v.length >= 8 ? '' : 'Пароль — не менее 8 символов'); },
        fio: function (v) { return !v ? 'Введите ФИО' : (/^[А-Яа-яЁё\s-]+$/.test(v) ? '' : 'Только кириллица и пробелы'); },
        phone: function (v) { return !v ? 'Введите телефон' : (/^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/.test(v) ? '' : 'Формат: 8(XXX)XXX-XX-XX'); },
        email: function (v) { return !v ? 'Введите e-mail' : (/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v) ? '' : 'Некорректный e-mail'); },
        future: function (v) {
            if (!v) return 'Укажите дату и время начала';
            var parts = /^(\d{2})\.(\d{2})\.(\d{4}) (\d{2}):(\d{2})$/.exec(v);
            if (!parts) return 'Формат: ДД.ММ.ГГГГ ЧЧ:ММ';
            var date = new Date(+parts[3], +parts[2] - 1, +parts[1], +parts[4], +parts[5]);
            if (date.getFullYear() !== +parts[3] || date.getMonth() !== +parts[2] - 1 || date.getDate() !== +parts[1] || date.getHours() !== +parts[4] || date.getMinutes() !== +parts[5]) return 'Некорректная дата';
            return date > new Date() ? '' : 'Дата начала должна быть в будущем';
        },
        min10: function (v) { return v.length >= 10 ? '' : 'Напишите отзыв — не короче 10 символов'; }
    };
    var names = { room_id: 'Выберите помещение', payment_type_id: 'Выберите способ оплаты', title: 'Введите название мероприятия', rating: 'Поставьте оценку', login: 'Введите логин', password: 'Введите пароль' };

    function valueOf(form, input) {
        if (input.type === 'radio') {
            var c = form.querySelector('input[name="' + input.name + '"]:checked');
            return c ? c.value : '';
        }
        return input.value.trim();
    }

    function showError(form, name, text) {
        var box = form.querySelector('[data-error="' + name + '"]');
        var input = form.querySelector('[name="' + name + '"]');
        var wrap = input && (input.closest('.field') || input.closest('fieldset'));
        if (box) box.textContent = text;
        if (wrap) wrap.classList.toggle('has-error', !!text);
    }

    function check(form, input) {
        var rule = input.dataset.rule;
        var v = valueOf(form, input);
        var msg = rules[rule](v);
        if (rule === 'required' && msg && names[input.name]) msg = names[input.name];
        if (input.name === 'body') { showError(form, 'rating', msg); return !msg; }
        showError(form, input.name, msg);
        return !msg;
    }

    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        var inputs = form.querySelectorAll('[data-rule]');
        inputs.forEach(function (input) {
            input.addEventListener(input.type === 'radio' ? 'change' : 'blur', function () { check(form, input); });
        });
        form.addEventListener('submit', function (e) {
            var ok = true;
            var seen = {};
            inputs.forEach(function (input) {
                if (input.type === 'radio') { if (seen[input.name]) return; seen[input.name] = 1; }
                if (!check(form, input)) ok = false;
            });
            if (!ok) {
                e.preventDefault();
                var first = form.querySelector('.has-error input, .has-error select, .has-error textarea');
                if (first) first.focus();
                return;
            }
            var submit = form.querySelector('[type="submit"]');
            if (submit) { submit.classList.add('is-busy'); }
        });
    });

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var b = form.querySelector('button');
            if (!form.dataset.armed) {
                e.preventDefault();
                form.dataset.armed = '1';
                b.textContent = 'Точно отменить?';
                setTimeout(function () { delete form.dataset.armed; b.textContent = 'Отменить'; }, 4000);
            }
        });
    });

    document.querySelectorAll('form[data-autosubmit] select').forEach(function (s) {
        s.addEventListener('change', function () { s.form.submit(); });
    });
})();
