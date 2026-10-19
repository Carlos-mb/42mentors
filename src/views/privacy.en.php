<?php
/**
 * Texto de la política de privacidad en inglés (lo incluye public/privacy.php). La versión en español es privacy.es.php:
 * si cambias una, cambia la otra y sube PRIVACY_VERSION y PRIVACY_UPDATED en src/auth.php.
 * @var ?array $user  @var int $retention
 */
?>
<section class="card">
    <h1>Privacy and terms of use</h1>
    <p class="muted">Version <?= PRIVACY_VERSION ?> · Last updated: <?= e(long_date(PRIVACY_UPDATED)) ?></p>
    <p class="muted small">This is a translation of the Spanish text. In case of discrepancy, the Spanish version prevails.</p>

    <h2>Controller and contact</h2>
    <p>Carlos Melero (login <code>cmelero-</code>) is responsible for this website, built with the 42 API in accordance
        with its General Terms of Use. To exercise your rights, withdraw your consent or ask anything about your data,
        write to <a href="mailto:42mentors@carlosmelero.com">42mentors@carlosmelero.com</a>.
        You will get an answer within one month at most.</p>

    <h2>Purpose</h2>
    <p>Only to help 42 students find mentors for their projects. The data is only visible to users who have logged in
        with 42. It is not shared with anyone, nor used for commercial purposes, advertising or automated decisions
        that affect you.</p>

    <h2>What data we store</h2>
    <p>Only from those who agree to it in order to be mentors or to rate a mentor, and only after they agree:</p>
    <ul>
        <li>42 identifier and login.</li>
        <li>Main campus.</li>
        <li>Projects they offer to mentor and, if the mentor writes one, a note about each of them.</li>
        <li>What the mentor writes in their profile, all optional: introduction, availability, preferred way of
            contact and languages.</li>
        <li>“It helped me” ratings: who rates which mentor, for which project, with how many points (1 to 3) and
            when. They are anonymous: others only see each mentor's total points, never who voted or when.</li>
        <li>Record of your acceptance: the date (“mentor since”) and the version of this policy you accepted.</li>
        <li>Date of your last login.</li>
    </ul>
    <p>If you declare yourself an official mentor or a manguito of Operation Manguito (with a private link given by the
        person responsible for the website), we store that mark and, if you are a mentor, the time until which you say
        you are available. An official mentor is visible to the logged-in students of their campus, in “Available now”,
        with their name, photo and seat in the cluster. Being a manguito is only visible to that person and to the
        person responsible. This mark was not requested by, nor is it managed by, 42 Madrid. You can remove it whenever
        you want from that same page, and the person responsible can remove it from anyone who misuses it.</p>
    <?php if (!votes_enabled()): ?>
        <p>“It helped me” ratings are currently turned off. Those already stored are kept without being shown, and they
            are deleted with “Delete my data”.</p>
    <?php endif; ?>

    <h2>What data we read from the 42 API</h2>
    <p>When you log in, the intra gives us your login, name, photo, campus and its time zone, and your projects with
        their status and whether they are validated. We use this to know who you are, which projects you have finished
        and which campus you belong to. All of it lives only in your session and is not stored in the database.</p>
    <p>For mentors who have accepted, when someone opens their profile or a list, we look up their name, photo, level,
        coalition, seat in the cluster, and the mark and validation date of the projects they mentor. None of this is
        stored in the database.</p>
    <p>To avoid overloading the API, that mentor data is kept in a temporary cache on the server: seats are refreshed
        every few minutes, and the rest at most once an hour when someone looks them up. Your 42 access token only
        lives in your session and disappears when you log out.</p>

    <h2>Legal basis</h2>
    <p>Your consent (art. 6.1.a GDPR). You give it on the “Before we store your data” screen, by ticking the box and
        clicking “I accept”; it is recorded with its date and the version of this policy. The same applies to marking
        yourself as an official mentor or manguito: you can only do it after accepting. The intra data shown about a
        mentor (name, photo, level…) is only shown if that mentor has given their consent. You can withdraw it at any
        time, without affecting anything done before.</p>
    <p>If what we store or how we use it changes in a relevant way, you will be asked to accept the policy again.</p>

    <h2>Who can use the website</h2>
    <p>Anyone with an account in the 42 network can log in with it. Each person only sees mentors and students of their
        main campus. People without a 42 account cannot get in.</p>

    <h2>Terms of use</h2>
    <ul>
        <li>It is for members of the 42 network who have logged in with their 42 account.</li>
        <li>The data on this website comes from the 42 API and from the mentor community, and must stay within the 42
            network: do not share, publish or redistribute it outside.</li>
        <li>Mass data extraction (scraping), automated access and attempts to get around access controls or API limits
            are forbidden.</li>
        <li>Your session is personal: do not share it or give access to anyone else.</li>
        <li>What you write in your profile (introduction, notes) must be respectful and must not contain other people's
            data.</li>
        <li>There is no guarantee about the help mentors give: mentoring is voluntary. Data may be minutes or hours out
            of date, and the official source is always the intra.</li>
        <li>Misuse may lead to access being withdrawn and reported to 42.</li>
    </ul>

    <h2>How long we keep it</h2>
    <ul>
        <li>Your profile, the projects you mentor, your ratings (given and received) and the record of your acceptance:
            for as long as you keep using the website. If you go <strong><?= $retention ?> months without logging
            in</strong>, they are deleted automatically. Before that, whenever you want, with “Delete my data”.</li>
        <li>Cache of intra data (name, photo, level…): it is refreshed from the intra as explained above and deleted
            when your data is deleted.</li>
        <li>Session: until you log out or your 42 access token expires (usually after 2 hours).</li>
        <li>If we shut the service down, we will delete the database.</li>
    </ul>

    <h2>Security</h2>
    <ul>
        <li>The connection is encrypted (HTTPS, with HSTS).</li>
        <li>Your 42 access token only lives in your session on the server: it is not stored in the database and
            disappears when you log out or when it expires.</li>
        <li>Cookies are <code>Secure</code>, <code>HttpOnly</code> and <code>SameSite</code>.</li>
        <li>Every page with data requires logging in with 42, and forms are protected against forged requests (CSRF).</li>
        <li>The application and database keys are kept outside the public part of the server and are not published in
            the code repository.</li>
        <li>We store the minimum: what you see in “What data we store”.</li>
        <li>The hosting provider makes automatic backups of the server. If you delete your data, it may remain in those
            backups until the provider replaces them with newer ones.</li>
    </ul>
    <p>If there were a security breach affecting personal data, 42 and, where appropriate, the people affected would be
        notified, and the compromised credentials would be revoked.</p>

    <h2>Cookies and local storage</h2>
    <p>We use two cookies, and neither stores personal data:</p>
    <ul>
        <li>The session cookie (<code>ft_mentors</code>), needed to keep you logged in: it is deleted when you close the
            browser or log out.</li>
        <li>The language cookie (<code>lang</code>): it is only created if you choose a language in the selector at the
            top, and it stores that choice (“es” or “en”) for one year. Without it, the website uses your browser's
            language.</li>
    </ul>
    <p>We do not use analytics or advertising cookies, nor third-party trackers, and we store nothing else in your
        browser (<code>localStorage</code>).</p>

    <h2>Third parties</h2>
    <p>No data is shared with third parties. Only these are involved:</p>
    <ul>
        <li>The 42 API (the intra), which you log in with and from which we read the data explained above, under 42's
            terms.</li>
        <li><code>cdn.intra.42.fr</code>, 42's CDN: profile photos are loaded from there, so your browser sends it
            technical connection data (such as your IP address) when requesting them.</li>
        <li>The server's hosting provider, which processes technical connection data (IP address and request headers)
            in its logs.</li>
    </ul>

    <h2>Your rights</h2>
    <p>You can exercise the rights in arts. 15 to 22 of the GDPR. Access, portability, rectification and erasure are
        available right here on the website; for any other, write to the contact above.</p>
    <ul>
        <li><strong>Access:</strong> the “Download my data” button below gives you a copy of everything we store about
            you.</li>
        <li><strong>Rectification:</strong> you can change what you wrote in <a href="profile.php">My profile</a>; data
            that comes from the intra is corrected there. You can see and change your ratings in
            <a href="ratings.php">My ratings</a>.</li>
        <li><strong>Erasure:</strong> the button below deletes all your data, including the ratings you have given and
            received.</li>
        <li><strong>Restriction and objection:</strong> you can set yourself to “paused” so you do not appear in
            searches, or untick all your projects. If you want anything else, write to us.</li>
        <li><strong>Portability:</strong> that same download is a JSON file, a structured and commonly used format. It
            includes what we store about you; it does not include intra data, which is not stored, nor who has rated
            you, because those ratings are anonymous (only the total points).</li>
        <li><strong>Withdrawing your consent:</strong> at any time, by deleting your data; it does not affect anything
            done before.</li>
    </ul>
    <p>If you believe we are not handling your data properly, you can file a complaint with the Spanish Data Protection
        Agency (AEPD, <a href="https://www.aepd.es" target="_blank" rel="noopener">www.aepd.es</a>).</p>

    <?php if ($user && user_has_data($user['id'])): ?>
        <div class="actions">
            <form method="post" action="download.php">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <button type="submit" class="btn">Download my data</button>
            </form>
            <form method="post" onsubmit="return confirm('Are you sure? Your data and your ratings will be deleted, and you will no longer appear as a mentor.');">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <button type="submit" class="btn btn-danger">Delete my data</button>
            </form>
        </div>
    <?php elseif ($user): ?>
        <p class="muted">We have no data of yours stored.</p>
    <?php else: ?>
        <p class="muted">Log in to manage your data.</p>
    <?php endif; ?>
</section>
