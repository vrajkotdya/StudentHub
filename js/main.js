/**
 * StudentHub — Main Script (js/main.js)
 * Coordinates common UI interactivity across pages and prevents accidental
 * PHP file downloads when browsing via the file:// protocol.
 */

document.addEventListener('DOMContentLoaded', function () {
    const isFileProtocol = window.location.protocol === 'file:';

    if (isFileProtocol) {
        // Intercept any forms targeting .php scripts to prevent browser file downloads
        const phpForms = document.querySelectorAll('form[action$=".php"]');
        phpForms.forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                alert(
                    "[Notice] Cannot execute PHP over the file:// protocol!\n\n" +
                    "Web browsers cannot execute PHP or connect to MySQL without a local web server (WAMP).\n" +
                    "Submitting now would only download the PHP file.\n\n" +
                    "Please open StudentHub via WAMP in your browser:\n" +
                    "http://localhost:8080/StudentHub/"
                );
            });
        });
    }

    console.log('StudentHub Portal UI Scripts Initialized.');
});
