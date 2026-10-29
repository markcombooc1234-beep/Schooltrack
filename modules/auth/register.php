<section class="login panel">
    <div class="brand">▦ SchoolTrak</div>
    <p class="subtitle">SCHOOL INVENTORY SYSTEM</p>
    <h1>Create account</h1>
    <p>Create a staff account to browse inventory and borrow school resources.</p>
    <?php startForm('register'); ?>
        <label>Full name
            <input name="name" value="<?= e($_SESSION['registration_input']['name'] ?? '') ?>" required maxlength="255" autocomplete="name">
        </label>
        <label>Email address
            <input name="email" type="email" value="<?= e($_SESSION['registration_input']['email'] ?? '') ?>" required maxlength="255" autocomplete="email">
        </label>
        <label>Password
            <input name="password" type="password" minlength="10" required autocomplete="new-password">
        </label>
        <small>Use at least 10 characters.</small>
        <label>Confirm password
            <input name="password_confirmation" type="password" minlength="10" required autocomplete="new-password">
        </label>
    <?php endForm('Create account'); ?>
    <p class="auth-link">Already have an account? <a href="?page=login">Sign in</a></p>
</section>
<?php unset($_SESSION['registration_input']); ?>
