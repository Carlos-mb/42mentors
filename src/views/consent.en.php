<?php
/**
 * Texto del consentimiento en inglés (lo incluye public/consent.php). La versión en español es consent.es.php:
 * si cambias una, cambia la otra.
 * @var array $user  @var string $next
 */
?>
<section class="card">
    <h1>Before we store your data</h1>
    <p>To offer yourself as a mentor or to rate a mentor, we need to store some of your data.
        We store <strong>only this</strong>:</p>
    <ul>
        <li>Your 42 identifier and login.</li>
        <li>Your main campus, so we only show you mentors and students from your campus.</li>
        <li>If you offer yourself as a mentor: the projects you mark as “I can mentor” and what you write in your
            profile, all optional (introduction, availability, how you prefer to be contacted, languages and notes
            about the projects).</li>
        <?php if (votes_enabled()): ?>
            <li>If you rate a mentor with “It helped me”: who, for which project, the points (1 to 3) and the date.</li>
        <?php endif; ?>
    </ul>
    <?php if ($next === 'mentor-manguito.php' || ($user['role'] ?? null) === 'mentor'): ?>
        <p>As an official mentor of Operation Manguito, we store that mark and the time until which you have said you
            are available. It is visible to the logged-in students of your campus, in “Available now”, together with
            your name, photo and seat in the cluster.</p>
    <?php elseif ($next === 'soy-manguito.php'): ?>
        <p>If you mark yourself as a manguito, we store that mark. Only you and the person responsible for the website
            can see it.</p>
    <?php endif; ?>
    <?php if (votes_enabled()): ?>
        <p>Ratings are anonymous: others only see each mentor's total points, never who voted.
            You can see and change yours in “My ratings”.</p>
    <?php endif; ?>
    <p>If you offer yourself as a mentor, the students of your campus who have logged in with 42 will see on your
        profile:</p>
    <ul>
        <li>What you write in your profile and the projects you mentor.</li>
        <li>Your name, photo, level, coalition, your seat in the cluster if you are logged in there, and the mark and
            validation date of the projects you mentor.</li>
        <?php if (votes_enabled()): ?>
            <li>The “It helped me” points you have received.</li>
        <?php endif; ?>
    </ul>
    <p>That intra data is read from the 42 API and is not stored in the database. To avoid overloading the API, a copy
        is kept in a temporary cache on the server, which is refreshed from the intra at most once an hour when someone
        looks it up, and which is deleted if you delete your data.</p>
    <p>You can set yourself to “paused”, or withdraw your consent and delete your data whenever you want, from the
        <a href="privacy.php" target="_blank" rel="noopener">privacy page</a>. The ratings you have given and received
        are deleted too. If you go <?= USER_RETENTION_MONTHS ?> months without logging in, they are deleted
        automatically.</p>
    <form method="post" class="actions">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="next" value="<?= e($next) ?>">
        <label class="check">
            <input type="checkbox" name="agree" value="1" required>
            I have read the <a href="privacy.php" target="_blank" rel="noopener">privacy policy and terms of use</a>
            and I agree to this data being stored.
        </label>
        <button type="submit" name="accept" value="1" class="btn">I accept</button>
        <button type="submit" name="accept" value="0" formnovalidate class="link">No, thanks</button>
    </form>
</section>
