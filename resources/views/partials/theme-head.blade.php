<script>
    window.tailwind = window.tailwind || {};
    window.tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                colors: {
                    cappias: {
                        green: '#10B981',
                        blue: '#1E3A8A',
                        dark: '#0F172A',
                        surface: '#1E293B'
                    }
                }
            }
        }
    };

    try {
        if (localStorage.getItem('cappias-theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    } catch (error) {
        // The interface remains usable when browser storage is unavailable.
    }
</script>
<script src="https://cdn.tailwindcss.com"></script>
<style>
    button {
        border-radius: .5rem;
        font-weight: 600;
        box-shadow: 0 1px 2px rgb(15 23 42 / .12);
        transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
        transition-duration: 200ms;
    }

    .dark body {
        background-color: #0F172A !important;
        color: #F1F5F9;
    }

    .dark .bg-white,
    .dark .bg-white\/95,
    .dark .bg-gray-50,
    .dark .bg-slate-50,
    .dark .bg-gray-100,
    .dark .bg-slate-100 {
        background-color: #1E293B !important;
    }

    .dark .bg-gray-200,
    .dark .bg-gray-300,
    .dark .bg-gray-400,
    .dark .bg-slate-200,
    .dark .bg-slate-300,
    .dark .bg-slate-400 {
        background-color: #334155 !important;
    }

    .dark .bg-green-50,
    .dark .bg-green-100,
    .dark .bg-green-200 {
        background-color: #064E3B !important;
    }

    .dark .bg-red-50,
    .dark .bg-red-100,
    .dark .bg-red-200 {
        background-color: #7F1D1D !important;
    }

    .dark .bg-yellow-50,
    .dark .bg-yellow-100,
    .dark .bg-yellow-200 {
        background-color: #713F12 !important;
    }

    .dark .bg-blue-50,
    .dark .bg-blue-100,
    .dark .bg-blue-200 {
        background-color: #1E3A8A !important;
    }

    .dark .bg-indigo-50,
    .dark .bg-indigo-100,
    .dark .bg-indigo-200 {
        background-color: #312E81 !important;
    }

    .dark .bg-purple-50,
    .dark .bg-purple-100,
    .dark .bg-purple-200 {
        background-color: #581C87 !important;
    }

    .dark .bg-orange-50,
    .dark .bg-orange-100,
    .dark .bg-orange-200 {
        background-color: #7C2D12 !important;
    }

    .dark .text-gray-900,
    .dark .text-gray-800,
    .dark .text-gray-700,
    .dark .text-slate-900,
    .dark .text-slate-800,
    .dark .text-slate-700 {
        color: #F1F5F9 !important;
    }

    .dark .text-gray-600,
    .dark .text-gray-500,
    .dark .text-gray-400,
    .dark .text-slate-600,
    .dark .text-slate-500,
    .dark .text-slate-400 {
        color: #CBD5E1 !important;
    }

    .dark .text-green-600,
    .dark .text-green-700,
    .dark .text-green-800 {
        color: #A7F3D0 !important;
    }

    .dark .text-blue-600,
    .dark .text-blue-700,
    .dark .text-blue-800 {
        color: #BFDBFE !important;
    }

    .dark .text-red-600,
    .dark .text-red-700,
    .dark .text-red-800 {
        color: #FECACA !important;
    }

    .dark .text-yellow-600,
    .dark .text-yellow-700,
    .dark .text-yellow-800 {
        color: #FDE68A !important;
    }

    .dark .text-indigo-600,
    .dark .text-indigo-700,
    .dark .text-indigo-800 {
        color: #C7D2FE !important;
    }

    .dark .text-purple-600,
    .dark .text-purple-700,
    .dark .text-purple-800 {
        color: #E9D5FF !important;
    }

    .dark .text-orange-600,
    .dark .text-orange-700,
    .dark .text-orange-800 {
        color: #FED7AA !important;
    }

    .dark .border-gray-100,
    .dark .border-gray-200,
    .dark .border-gray-300,
    .dark .border-gray-400,
    .dark .border-slate-100,
    .dark .border-slate-200,
    .dark .border-slate-300,
    .dark .border-slate-400 {
        border-color: #475569 !important;
    }

    .dark .border-green-200,
    .dark .border-green-300,
    .dark .border-green-400 {
        border-color: #059669 !important;
    }

    .dark .border-red-200,
    .dark .border-red-300,
    .dark .border-red-400 {
        border-color: #DC2626 !important;
    }

    .dark .border-yellow-200,
    .dark .border-yellow-300,
    .dark .border-yellow-400 {
        border-color: #A16207 !important;
    }

    .dark .border-blue-200,
    .dark .border-blue-300,
    .dark .border-blue-400 {
        border-color: #2563EB !important;
    }

    .dark .border-indigo-200,
    .dark .border-indigo-300,
    .dark .border-indigo-400 {
        border-color: #4F46E5 !important;
    }

    .dark .border-purple-200,
    .dark .border-purple-300,
    .dark .border-purple-400 {
        border-color: #9333EA !important;
    }

    .dark .border-orange-200,
    .dark .border-orange-300,
    .dark .border-orange-400 {
        border-color: #C2410C !important;
    }

    .dark table,
    .dark th,
    .dark td {
        border-color: #475569;
    }

    .dark .hover\:bg-gray-50:hover,
    .dark .hover\:bg-gray-100:hover,
    .dark .hover\:bg-slate-50:hover,
    .dark .hover\:bg-gray-300:hover {
        background-color: #334155 !important;
    }

    .dark .hover\:bg-green-700:hover {
        background-color: #10B981 !important;
    }

    .dark .hover\:bg-blue-700:hover {
        background-color: #3B82F6 !important;
    }

    .dark .hover\:bg-red-700:hover {
        background-color: #B91C1C !important;
    }

    .dark input:not([type="checkbox"]):not([type="radio"]),
    .dark select,
    .dark textarea {
        background-color: #0F172A;
        border-color: #475569;
        color: #F8FAFC;
    }

    .dark input::placeholder,
    .dark textarea::placeholder {
        color: #94A3B8;
    }

    .bg-green-600 {
        background-color: #10B981 !important;
    }

    .hover\:bg-green-700:hover {
        background-color: #059669 !important;
    }

    .bg-blue-600 {
        background-color: #1E3A8A !important;
    }

    .hover\:bg-blue-700:hover {
        background-color: #1E40AF !important;
    }

    .bg-slate-800 {
        background-color: #1E3A8A !important;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const updateThemeButtons = function () {
            document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
                const isDark = document.documentElement.classList.contains('dark');
                const icon = button.querySelector('i');
                button.setAttribute('aria-label', isDark ? 'Activar modo claro' : 'Activar modo oscuro');
                button.setAttribute('aria-pressed', isDark ? 'true' : 'false');
                button.title = isDark ? 'Activar modo claro' : 'Activar modo oscuro';

                if (icon) {
                    icon.classList.toggle('fa-moon', !isDark);
                    icon.classList.toggle('fa-sun', isDark);
                }
            });
        };

        document.addEventListener('click', function (event) {
            const button = event.target.closest('[data-theme-toggle]');
            if (!button) return;

            const isDark = document.documentElement.classList.toggle('dark');
            try {
                localStorage.setItem('cappias-theme', isDark ? 'dark' : 'light');
            } catch (error) {
                // Theme still changes for the current page when storage is unavailable.
            }
            updateThemeButtons();
        });

        updateThemeButtons();
    });
</script>
