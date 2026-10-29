/**
 * CINEHUB - Interakcje Frontendowe i Walidacja
 * Rola: Frontend Developer
 */

document.addEventListener('DOMContentLoaded', () => {
    // --------------------------------------------------------------------------
    // 1. Interaktywny Selektor Oceny (Rating Picker)
    // --------------------------------------------------------------------------
    const ratingInputs = document.querySelectorAll('input[name="ocena"]');
    const ratingPreview = document.getElementById('rating-preview-text');

    const ratingDescriptions = {
        10: '10/10 – Arcydzieło',
        9: '9/10 – Rewelacyjny',
        8: '8/10 – Bardzo dobry',
        7: '7/10 – Dobry',
        6: '6/10 – Niezły',
        5: '5/5 – Przeciętny',
        4: '4/10 – Ujdzie w tłoku',
        3: '3/10 – Słaby',
        2: '2/10 – Bardzo zły',
        1: '1/10 – Nieporozumienie'
    };

    function updateRatingPreview(value) {
        if (ratingPreview && ratingDescriptions[value]) {
            ratingPreview.textContent = `★ ${ratingDescriptions[value]}`;
        }
    }

    if (ratingInputs.length > 0) {
        ratingInputs.forEach(input => {
            if (input.checked) {
                updateRatingPreview(input.value);
            }
            input.addEventListener('change', (e) => {
                updateRatingPreview(e.target.value);
            });
        });
    }

    // --------------------------------------------------------------------------
    // 2. Licznik Znaków Recenzji
    // --------------------------------------------------------------------------
    const reviewTextarea = document.getElementById('tresc');
    const charCounter = document.getElementById('char-count');

    if (reviewTextarea && charCounter) {
        const updateCounter = () => {
            const length = reviewTextarea.value.trim().length;
            charCounter.textContent = `${length} znaków (min. 5)`;
            if (length < 5 && length > 0) {
                charCounter.style.color = 'var(--error-text)';
            } else {
                charCounter.style.color = 'var(--text-dark)';
            }
        };

        reviewTextarea.addEventListener('input', updateCounter);
        updateCounter();
    }

    // --------------------------------------------------------------------------
    // 3. Walidacja Formularza Recenzji przed Wysłaniem
    // --------------------------------------------------------------------------
    const reviewForm = document.getElementById('review-form');

    if (reviewForm) {
        reviewForm.addEventListener('submit', (e) => {
            const autorInput = document.getElementById('autor');
            const trescInput = document.getElementById('tresc');
            let hasError = false;
            let errorMessage = '';

            if (!autorInput || autorInput.value.trim().length === 0) {
                errorMessage = 'Proszę podać imię lub pseudonim.';
                autorInput?.focus();
                hasError = true;
            } else if (!trescInput || trescInput.value.trim().length < 5) {
                errorMessage = 'Treść recenzji musi zawierać co najmniej 5 znaków.';
                trescInput?.focus();
                hasError = true;
            }

            if (hasError) {
                e.preventDefault();
                alert(errorMessage);
            }
        });
    }
});

