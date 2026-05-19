/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

document.addEventListener('DOMContentLoaded', function () {
    const projectsTable = document.getElementById('projects-table');
    if (!projectsTable) {
        return;
    }

    if (window.jQuery && $.fn.dataTable) {
        $('#projects-table').DataTable({
            pageLength: 25,
            order: [[2, 'desc']],
            columns: [
                { orderable: false },
                null,
                null
            ]
        });
    }
});
