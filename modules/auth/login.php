<section class="login panel">
    <div class="brand">▦ SchoolTrak</div>
    <p class="subtitle">SCHOOL INVENTORY SYSTEM</p>
    <h1>Welcome back</h1>
    <p>Sign in to manage your school resources.</p>
    <?php
    startForm('login');
    field('email', 'Email address', '', 'email');
    field('password', 'Password', '', 'password');
    endForm('Sign in');
    ?>
    <p class="auth-link">New to SchoolTrak? <a href="?page=register">Create account</a></p>
    <p class="auth-link"><a href="install.php">First-time administrator setup</a></p>
</section>
