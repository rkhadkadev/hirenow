<?php
use PHPMailer\PHPMailer\Exception as MailerException;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../vendor/autoload.php';

$services = [
    'Plumbing',
    'Electrician',
    'Sweeper',
    'House cleaning / Trash removal',
    'Aged care',
];

$errors = [];
$success = false;

$values = [
    'name' => '',
    'service' => '',
    'term' => '',
    'days_per_week' => '',
    'total_days' => '',
    'contact' => '',
    'message' => '',
];

loadEnvFile(__DIR__ . '/../.env');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $unused) {
        $values[$key] = trim((string) filter_input(INPUT_POST, $key, FILTER_UNSAFE_RAW));
    }

    if ($values['name'] === '') {
        $errors[] = 'Please enter your name.';
    }

    if (!in_array($values['service'], $services, true)) {
        $errors[] = 'Please choose a service.';
    }

    if (!in_array($values['term'], ['Long term', 'Short term'], true)) {
        $errors[] = 'Please choose long term or short term support.';
    }

    if ($values['term'] === 'Long term') {
        $days = filter_var($values['days_per_week'], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 7],
        ]);

        if ($days === false) {
            $errors[] = 'Please enter how many days per week, from 1 to 7.';
        }
    }

    if ($values['term'] === 'Short term') {
        $days = filter_var($values['total_days'], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 365],
        ]);

        if ($days === false) {
            $errors[] = 'Please enter the total number of days needed.';
        }
    }

    if ($values['contact'] === '') {
        $errors[] = 'Please enter your email address or phone number.';
    }

    if ($values['message'] === '') {
        $errors[] = 'Please add a detailed message.';
    }

    if ($errors === []) {
        $recipient = getenv('CONTACT_EMAIL') ?: 'info@hirenow.buildprocure.com';
        $subject = 'New HireNow service request';
        $from = getenv('MAIL_FROM') ?: 'no-reply@hirenow.buildprocure.com';
        $fromName = getenv('MAIL_FROM_NAME') ?: 'HireNow Website';

        $duration = $values['term'] === 'Long term'
            ? $values['days_per_week'] . ' day(s) per week'
            : $values['total_days'] . ' total day(s)';

        $body = implode("\n", [
            'New service request from HireNow website',
            '',
            'Name: ' . $values['name'],
            'Service: ' . $values['service'],
            'Term: ' . $values['term'],
            'Duration: ' . $duration,
            'Contact: ' . $values['contact'],
            '',
            'Message:',
            $values['message'],
        ]);

        try {
            sendServiceRequestEmail($recipient, $subject, $body, $from, $fromName, $values['contact']);
            $success = true;
        } catch (MailerException $exception) {
            error_log('HireNow mail error: ' . $exception->getMessage());
            $success = false;
        }

        if ($success) {
            foreach ($values as $key => $unused) {
                $values[$key] = '';
            }
        } else {
            $errors[] = 'We could not send your request right now. Please call or email us directly.';
        }
    }
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function loadEnvFile(string $path): void
{
    if (!is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines ?: [] as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");

        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

function sendServiceRequestEmail(
    string $recipient,
    string $subject,
    string $body,
    string $from,
    string $fromName,
    string $replyTo
): void {
    $host = getenv('SMTP_HOST') ?: '';
    $username = getenv('SMTP_USER') ?: '';
    $password = getenv('SMTP_PASSWORD') ?: '';
    $port = (int) (getenv('SMTP_PORT') ?: 587);
    $secure = strtolower((string) (getenv('SMTP_SECURE') ?: 'tls'));

    if ($host === '' || $username === '' || $password === '') {
        throw new MailerException('SMTP settings are incomplete.');
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->Host = $host;
    $mail->Username = $username;
    $mail->Password = $password;
    $mail->Port = $port;
    $mail->CharSet = 'UTF-8';

    if ($secure === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }

    $mail->setFrom($from, $fromName);
    $mail->addAddress($recipient);

    if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $mail->addReplyTo($replyTo);
    }

    $mail->Subject = $subject;
    $mail->Body = $body;
    $mail->AltBody = $body;
    $mail->send();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HireNow Professional Services</title>
    <meta name="description" content="HireNow connects homes and businesses in Manassas, Virginia with trusted plumbing, electrical, cleaning, trash removal, sweeping, and aged care support.">
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="#top" aria-label="HireNow home">HireNow</a>
        <nav aria-label="Primary navigation">
            <a href="#services">Services</a>
            <a href="#request">Request Help</a>
            <a href="#contact">Contact</a>
        </nav>
    </header>

    <main id="top">
        <section class="hero">
            <div class="hero-content">
                <p class="eyebrow">Manassas, Virginia</p>
                <h1>Reliable professionals for everyday home and care needs.</h1>
                <p>HireNow helps you request skilled local support for repairs, cleaning, trash removal, sweeping, and aged care. Tell us what you need, and we will follow up with the right next step.</p>
                <a class="button" href="#request">Contact us for more info</a>
            </div>
        </section>

        <section class="intro">
            <div>
                <p class="eyebrow">Introduction</p>
                <h2>Simple help, clearly arranged.</h2>
            </div>
            <p>Whether you need short-term help for a one-time job or regular weekly support, our team makes it easy to reach out and explain your requirement in one place.</p>
        </section>

        <section id="services" class="services">
            <div class="section-heading">
                <p class="eyebrow">Services We Provide</p>
                <h2>Professionals available for practical, trusted support.</h2>
            </div>
            <div class="service-grid">
                <?php foreach ($services as $service): ?>
                    <article class="service-card">
                        <h3><?= e($service) ?></h3>
                        <p><?= e(match ($service) {
                            'Plumbing' => 'Assistance for pipe, fixture, leak, and general plumbing needs.',
                            'Electrician' => 'Support for electrical repairs, installation needs, and troubleshooting.',
                            'Sweeper' => 'Sweeping support for residential and commercial spaces.',
                            'House cleaning / Trash removal' => 'Cleaning, decluttering, and trash removal for homes and workspaces.',
                            'Aged care' => 'Compassionate care assistance for older adults and their daily routines.',
                        }) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="request" class="request-section">
            <div class="request-copy">
                <p class="eyebrow">Contact Us For More Info</p>
                <h2>Tell us what kind of professional you need.</h2>
                <p>Submit the form and we will receive your request by email. Include timing, location details, and anything important about the job.</p>
            </div>

            <form class="contact-form" method="post" action="#request">
                <?php if ($success): ?>
                    <div class="notice success" role="status">Thank you. Your request has been sent.</div>
                <?php endif; ?>

                <?php if ($errors !== []): ?>
                    <div class="notice error" role="alert">
                        <strong>Please review:</strong>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <label>
                    Your name
                    <input type="text" name="name" value="<?= e($values['name']) ?>" autocomplete="name" required>
                </label>

                <label>
                    Professional service needed
                    <select name="service" required>
                        <option value="">Select a service</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?= e($service) ?>" <?= $values['service'] === $service ? 'selected' : '' ?>><?= e($service) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <fieldset>
                    <legend>Service duration</legend>
                    <div class="radio-row">
                        <label>
                            <input type="radio" name="term" value="Long term" <?= $values['term'] === 'Long term' ? 'checked' : '' ?> required>
                            Long term
                        </label>
                        <label>
                            <input type="radio" name="term" value="Short term" <?= $values['term'] === 'Short term' ? 'checked' : '' ?> required>
                            Short term
                        </label>
                    </div>
                </fieldset>

                <div class="conditional-field" data-term-field="Long term">
                    <label>
                        How many days a week?
                        <input type="number" name="days_per_week" value="<?= e($values['days_per_week']) ?>" min="1" max="7" inputmode="numeric">
                    </label>
                </div>

                <div class="conditional-field" data-term-field="Short term">
                    <label>
                        How many total days?
                        <input type="number" name="total_days" value="<?= e($values['total_days']) ?>" min="1" max="365" inputmode="numeric">
                    </label>
                </div>

                <label>
                    Email address or phone number
                    <input type="text" name="contact" value="<?= e($values['contact']) ?>" autocomplete="email tel" required>
                </label>

                <label>
                    Detailed message
                    <textarea name="message" rows="6" required><?= e($values['message']) ?></textarea>
                </label>

                <button type="submit">Submit request</button>
            </form>
        </section>

        <section id="contact" class="contact-block">
            <p class="eyebrow">Contact Us</p>
            <h2>HireNow</h2>
            <address>Manassas, Virginia</address>
            <p>Email: <a href="mailto:info@hirenow.buildprocure.com">info@hirenow.buildprocure.com</a></p>
        </section>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> HireNow. All rights reserved.</p>
    </footer>

    <script>
        const termInputs = document.querySelectorAll('input[name="term"]');
        const fields = document.querySelectorAll('[data-term-field]');

        function syncTermFields() {
            const selected = document.querySelector('input[name="term"]:checked')?.value;

            fields.forEach((field) => {
                const active = field.dataset.termField === selected;
                field.hidden = !active;
                field.querySelector('input').required = active;
            });
        }

        termInputs.forEach((input) => input.addEventListener('change', syncTermFields));
        syncTermFields();
    </script>
</body>
</html>
