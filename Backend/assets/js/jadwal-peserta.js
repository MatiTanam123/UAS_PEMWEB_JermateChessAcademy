document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form').forEach((form) => {
        const program = form.querySelector('select[name="id_program"]');
        const options = form.querySelectorAll('.schedule-participant-option');
        if (!program || !options.length) return;

        const filterParticipants = () => {
            const selectedProgram = program.value;
            options.forEach((option) => {
                const visible = option.dataset.program === selectedProgram;
                option.hidden = !visible;
                const checkbox = option.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.disabled = !visible;
                    if (!visible) checkbox.checked = false;
                }
            });
            const visibleOptions = [...options].filter((option) => !option.hidden);
            const empty = form.querySelector('.schedule-picker-empty');
            if (empty) empty.hidden = visibleOptions.length > 0;
        };

        program.addEventListener('change', filterParticipants);
        filterParticipants();
    });
});
