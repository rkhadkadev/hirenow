# HireNow Website

A simple PHP and Apache website for HireNow professional service requests.

## Services

- Plumbing
- Electrician
- Sweeper
- House cleaning / Trash removal
- Aged care

## Local Docker Run

```bash
docker compose up --build
```

The site will be available at `http://localhost:3100`.

## Email

The contact form sends email through PHPMailer SMTP. Set these values in `.env` for local Docker, or as GitHub repository secrets for deployment:

- `CONTACT_EMAIL`: where service requests are sent
- `MAIL_FROM`: sender address used by the website
- `MAIL_FROM_NAME`: sender name shown in inboxes
- `SMTP_HOST`: SMTP server host
- `SMTP_PORT`: SMTP server port, usually `587`
- `SMTP_SECURE`: `tls` or `ssl`
- `SMTP_USER`: SMTP username
- `SMTP_PASSWORD`: SMTP password

Copy `.env.example` to `.env` and fill in the real SMTP values before running Docker locally.

## GitHub Actions Secrets

The workflow builds and pushes the image to GHCR on pushes to `main`.

For DigitalOcean deployment, add these repository secrets:

- `DO_HOST`: DigitalOcean server IP or host
- `DO_USER`: SSH username
- `DO_SSH_KEY`: private SSH key for deployment
- `GHCR_TOKEN`: token with permission to pull the GHCR package
- `CONTACT_EMAIL`: destination email for requests
- `MAIL_FROM`: sender email address
- `MAIL_FROM_NAME`: sender name
- `SMTP_HOST`: SMTP server host
- `SMTP_PORT`: SMTP server port
- `SMTP_SECURE`: SMTP security mode, usually `tls`
- `SMTP_USER`: SMTP username
- `SMTP_PASSWORD`: SMTP password

## Reverse Proxy

Public traffic for `hirenow.buildprocure.com` is routed by the existing `buildprocure` Apache container. Keep the `hirenow` container on the shared external Docker network `app-network`, then add the `hirenow.buildprocure.com` virtual host to the `buildprocure` Apache configuration.
