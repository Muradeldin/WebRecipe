// js/recipe-calculator.js
// Moved from inline script in recipe-calculator.php
(function () {
    'use strict';

    function formatAmount(val) {
        if (!Number.isFinite(val)) return '';
        if (Math.abs(val - Math.round(val)) < 0.001) return String(Math.round(val));
        return parseFloat(val.toFixed(2)).toString();
    }

    function parseAmount(str) {
        const clean = (str || '').trim();
        if (!clean) return null;

        const frac = clean.match(/^(\d+)\s*\/\s*(\d+)$/);
        if (frac) {
            const num = Number(frac[1]);
            const den = Number(frac[2]) || 1;
            return num / den;
        }

        const num = Number(clean);
        return Number.isFinite(num) ? num : null;
    }

    function initializeServingsCalculator() {
        const control = document.getElementById('servingsControl');
        if (!control) return;

        const input = document.getElementById('servingsInput');
        const applyBtn = document.getElementById('servingsApply');
        const resetBtn = document.getElementById('servingsReset');
        const hint = document.getElementById('servingsHint');
        
        const baseServingsRaw = control.getAttribute('data-base-servings') || '';
        const baseServingsParsed = parseAmount(baseServingsRaw);
        const fallbackBaseServings = (baseServingsParsed !== null && baseServingsParsed > 0) ? baseServingsParsed : 1;

        const ingredientsList = document.getElementById('ingredientsList');
        const ingredients = Array.from(ingredientsList?.querySelectorAll('.ingredient-item') || []).map((item) => {
            const baseAmountRaw = item.getAttribute('data-base-amount') || '';
            const baseAmount = parseAmount(baseAmountRaw);
            return {
                item,
                baseAmount,
                baseAmountRaw,
                amountSpan: item.querySelector('.ingredient-amount')
            };
        });

        function applyScale() {
            const target = parseAmount(input?.value || '');
            if (!target || target <= 0) return;

            const factor = target / fallbackBaseServings;

            ingredients.forEach((item) => {
                if (item.baseAmount === null || !item.amountSpan) return;
                const scaled = item.baseAmount * factor;
                item.amountSpan.textContent = formatAmount(scaled);
            });

            updateHint(target);
        }

        function updateHint(target) {
            if (!hint) return;
            const baseText = baseServingsRaw !== '' ? baseServingsRaw : formatAmount(fallbackBaseServings);
            const currentText = target ? formatAmount(target) : baseText;
            hint.textContent = `מבוסס על ${baseText} מנות • עכשיו: ${currentText} מנות`;
        }

        function resetScale() {
            const base = baseServingsParsed || fallbackBaseServings;
            if (input) input.value = formatAmount(base);

            ingredients.forEach((item) => {
                if (item.amountSpan) item.amountSpan.textContent = item.baseAmountRaw;
            });

            hint.textContent = baseServingsRaw !== ''
                ? `מבוסס על ${baseServingsRaw} מנות`
                : `מבוסס על ${formatAmount(base)} מנות`;
        }

        applyBtn?.addEventListener('click', (ev) => {
            ev.preventDefault();
            applyScale();
        });

        input?.addEventListener('change', applyScale);

        resetBtn?.addEventListener('click', (ev) => {
            ev.preventDefault();
            resetScale();
        });

        resetScale();
    }

    document.addEventListener('DOMContentLoaded', initializeServingsCalculator);
})();
