/**
 * nav_session.js — VoltGrid
 * Include this in any HTML page that has nav links.
 * It calls check_session.php and swaps "Sign In" → "My Dashboard / Sign Out"
 * when the user has an active session or remember-me cookie.
 *
 * Usage: add before </body> on any .html page:
 *   <script src="nav_session.js"></script>
 *
 * Your nav must contain an element with id="nav-account-link":
 *   <a id="nav-account-link" href="index.html#signin">Sign In</a>
 */
(function () {
  fetch('check_session.php', { credentials: 'same-origin' })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (!data.logged_in) return;

      // Replace every "Sign In" nav link with Dashboard + Sign Out
      var targets = document.querySelectorAll(
        'a[href="index.html#signin"], a[href="#signin"], a[href="login.html"]'
      );

      targets.forEach(function (el) {
        // Replace the Sign In link with "Hi, Name" → dashboard
        el.textContent = 'Hi, ' + data.first_name;
        el.href = 'dashboard.php';
        el.title = data.email;

        // Insert a Sign Out link right after
        var out = document.createElement('a');
        out.href = 'logout.php';
        out.textContent = 'Sign Out';
        out.style.cssText = el.style.cssText; // inherit inline styles if any
        // Copy classes
        out.className = el.className;
        el.parentNode.insertBefore(out, el.nextSibling);
      });
    })
    .catch(function () {
      // Network error or PHP not running — silently do nothing
    });
})();
