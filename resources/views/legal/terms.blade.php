<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SplitMate Terms and Conditions</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f5f7fb;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #475569;
            --line: #e2e8f0;
            --accent: #0f766e;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(180deg, #eefcfb 0%, var(--bg) 100%);
            color: var(--text);
            line-height: 1.6;
        }
        .wrap {
            max-width: 920px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }
        .top {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .top a {
            color: #0f766e;
            text-decoration: none;
            font-weight: 700;
        }
        h1 { margin: 0 0 8px; font-size: 2rem; line-height: 1.2; }
        h2 { margin: 26px 0 10px; font-size: 1.2rem; line-height: 1.3; }
        p { margin: 10px 0; color: var(--muted); }
        ul { margin: 8px 0 8px 20px; color: var(--muted); }
        li { margin: 6px 0; }
        .meta {
            display: inline-block;
            margin: 8px 0 20px;
            padding: 5px 10px;
            border: 1px solid #b7ece8;
            background: #ecfdf5;
            color: #14532d;
            border-radius: 999px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .note {
            border-left: 4px solid var(--accent);
            padding: 10px 12px;
            margin: 14px 0;
            background: #f0fdfa;
            color: #134e4a;
            border-radius: 8px;
        }
        a { color: #0f766e; }
        .footer-brand {
            margin-top: 28px;
            padding-top: 16px;
            border-top: 1px solid var(--line);
            text-align: center;
            font-weight: 700;
            color: #0f172a;
        }
    </style>
</head>
<body>
    <main class="wrap">
        <article class="card">
            <div class="top">
                <a href="{{ url('/') }}">SplitMate Home</a>
                <a href="{{ route('legal.privacy') }}">Privacy Policy</a>
            </div>
            <h1>SplitMate Terms and Conditions</h1>
            <div class="meta">Last updated: April 27, 2026</div>

            <p>These Terms and Conditions govern your use of SplitMate mobile app and related services. By using SplitMate, you agree to these terms.</p>

            <h2>1. Use of Service</h2>
            <p>You may use SplitMate for personal or lawful business expense sharing. You agree not to misuse the app, interfere with service operation, or attempt unauthorized access.</p>

            <h2>2. Accounts</h2>
            <p>You are responsible for account credentials and activity on your account. You must provide accurate information and keep your login method secure.</p>

            <h2>3. Group and Expense Data</h2>
            <p>You are responsible for expense entries, settlements, and records you create or share in groups. SplitMate is not liable for disputes between group members.</p>

            <h2>4. Acceptable Conduct</h2>
            <ul>
                <li>No unlawful, fraudulent, or abusive use.</li>
                <li>No attempts to reverse engineer, disrupt, or exploit the app.</li>
                <li>No uploading harmful files or malicious content.</li>
            </ul>

            <h2>5. Service Availability</h2>
            <p>We may update, suspend, or discontinue features at any time. We do not guarantee uninterrupted or error-free operation.</p>

            <h2>6. Limitation of Liability</h2>
            <p>To the maximum extent permitted by law, SplitMate is provided "as is" without warranties. We are not liable for indirect, incidental, or consequential damages from service use.</p>

            <h2>7. Changes to Terms</h2>
            <p>We may revise these terms from time to time. Continued use after updates means you accept the revised terms.</p>

            <div class="note">If you do not agree with these terms, discontinue use of SplitMate services.</div>

            <h2>8. Contact</h2>
            <p>For legal questions, contact: <a href="mailto:contact@brainandbolt.com">contact@brainandbolt.com</a></p>

            <div class="footer-brand">
                Brainandbolt &middot; <a href="https://brainandbolt.com">brainandbolt.com</a>
            </div>
        </article>
    </main>
</body>
</html>
