<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Yatmo Laravel demo' }}</title>
    @isset($metaDescription)<meta name="description" content="{{ $metaDescription }}">@endisset
    <style>
        :root { --ink: #1c2430; --muted: #5c6773; --accent: #1f6f8b; --line: #e3e7eb; }
        * { box-sizing: border-box; }
        body { margin: 0; font: 16px/1.55 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; color: var(--ink); background: #f6f7f9; }
        header { background: #fff; border-bottom: 1px solid var(--line); }
        header .wrap { display: flex; align-items: center; justify-content: space-between; padding: 14px 0; }
        header a { color: var(--ink); text-decoration: none; font-weight: 600; }
        .wrap { max-width: 1040px; margin: 0 auto; padding: 0 20px; }
        main { padding: 28px 0 56px; }
        h1 { font-size: 30px; margin: 0 0 6px; }
        h2 { font-size: 20px; margin: 36px 0 12px; }
        .price { color: var(--accent); font-weight: 700; font-size: 22px; margin: 0 0 14px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .card { background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 18px; }
        .card a { color: var(--ink); text-decoration: none; font-weight: 600; }
        .yatmo-map { border-radius: 12px; }
        .yatmo-pois { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px 24px; }
        .yatmo-pois h3 { font-size: 15px; margin: 0 0 4px; color: var(--muted); font-weight: 600; }
        .yatmo-pois ul { list-style: none; margin: 0; padding: 0; }
        .yatmo-text h3 { font-size: 17px; margin: 22px 0 6px; }
        .yatmo-text p { margin: 0; color: #2b3540; }
        footer { color: var(--muted); font-size: 14px; }
    </style>
</head>
<body>
<header><div class="wrap"><a href="/">Maison Demo</a><span style="color:var(--muted);font-size:14px">yatmo/laravel example</span></div></header>
<main><div class="wrap">{{ $slot }}</div></main>
<footer><div class="wrap">Neighbourhood data by <a href="https://yatmo.com">Yatmo</a></div></footer>
</body>
</html>
