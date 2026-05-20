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
            processing: true,
            serverSide: true,
            pageLength: 25,
            order: [[2, 'desc']],
            ajax: {
                url: '/api/projects',
                type: 'GET'
            },
            columns: [
                { 
                    data: 'id', 
                    orderable: false,
                    render: function (data, type, row, meta) {
                        var globalIndex = meta.settings._iDisplayStart + meta.row + 1;
                        return '<th scope="row" class="ps-3 font-monospace text-muted">' + globalIndex + '</th>';
                    }
                },
                { 
                    data: 'name',
                    render: function (data, type, row) {
                        var projectUrl = '/project/' + row.id; 
                        return '<a href="' + projectUrl + '" class="fw-bold text-decoration-none">' + data + '</a>';
                    }
                },
                { 
                    data: 'stars',
                    render: function (data, type, row) {
                        return '<span class="badge bg-warning text-dark font-monospace fw-bold px-2 py-1">' + data + '</span>';
                    }
                }
            ]
        });
    }
});
