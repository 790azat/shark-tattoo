"""Синхронизация публичного контента Instagram в репозиторий.

Пишет database/data/instagram.json, картинки в public/ig/ (WebP), видео в videos/
(их workflow выкладывает в GitHub Release, на сайте они играют по ссылке).
Без входа Instagram отдаёт профиль и ленту публикаций. С переменной IG_SESSIONID
(cookie sessionid запасного аккаунта) дополнительно скачиваются актуальные и сторис.
Скрипт продолжает с места остановки, если Instagram временно ограничил запросы.
"""
import io, json, os, random, sys, time, urllib.error, urllib.request
from datetime import datetime, timezone

from PIL import Image

USER = os.environ.get('IG_USER', '_shark__tattoo')
REPO = os.environ.get('GITHUB_REPOSITORY', '790azat/shark-tattoo')
RELEASE_URL = f'https://github.com/{REPO}/releases/download/instagram-media/'
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DATA = os.path.join(ROOT, 'database/data/instagram.json')
STATE = os.path.join(ROOT, 'database/data/instagram_state.json')
PUB = os.path.join(ROOT, 'public')
VIDEOS = os.path.join(ROOT, 'videos')
# Миниатюры скрытых постов для ручного просмотра (артефакт workflow, в репозиторий не попадают)
REVIEW = os.path.join(ROOT, 'review')
DEADLINE = time.time() + float(os.environ.get('MAX_MINUTES', '300')) * 60
SESSION = os.environ.get('IG_SESSIONID', '').strip()

UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36'
HEADERS = {'User-Agent': UA, 'x-ig-app-id': '936619743392459', 'Accept': '*/*'}
if SESSION:
    HEADERS['Cookie'] = f'sessionid={SESSION}'

TATTOO_WORDS = ('tattoo', 'tatu', 'тату', 'տատու', 'դաջվածք', 'fine line', 'fineline', 'ink', 'sketch', 'эскиз')
PIERCING_WORDS = ('piercing', 'пирсинг', 'պիրսինգ', 'sharp_by_shark')


def log(*a):
    print(time.strftime('%H:%M:%S'), *a, flush=True)


def fetch(url, headers=None, tries=6):
    for attempt in range(tries):
        req = urllib.request.Request(url, headers=headers or HEADERS)
        try:
            with urllib.request.urlopen(req, timeout=60) as r:
                return r.read()
        except urllib.error.HTTPError as e:
            body = e.read()[:200]
            log('HTTP', e.code, url[:90], body)
            if e.code in (401, 429, 500, 502, 503) and time.time() < DEADLINE:
                time.sleep(min(600, 60 * (attempt + 1)) + random.uniform(0, 30))
                continue
            return None
        except Exception as e:  # сеть
            log('ERR', e)
            time.sleep(10)
    return None


def getjson(url):
    body = fetch(url)
    try:
        return json.loads(body) if body else None
    except ValueError:
        return None


def best(cands):
    return max(cands, key=lambda c: c.get('width', 0) * c.get('height', 0))['url'] if cands else None


def save_image(url, rel, size=1080):
    """Скачивает картинку и сохраняет WebP. Возвращает путь от корня сайта."""
    path = os.path.join(PUB, rel)
    if not os.path.exists(path):
        raw = fetch(url, {'User-Agent': UA})
        if not raw:
            return None
        os.makedirs(os.path.dirname(path), exist_ok=True)
        img = Image.open(io.BytesIO(raw)).convert('RGB')
        img.thumbnail((size, size * 2))
        img.save(path, 'WEBP', quality=80, method=6)
    return '/' + rel


def save_video(url, name):
    os.makedirs(VIDEOS, exist_ok=True)
    path = os.path.join(VIDEOS, name)
    if not os.path.exists(path):
        raw = fetch(url, {'User-Agent': UA})
        if not raw:
            return None
        with open(path, 'wb') as f:
            f.write(raw)
    return RELEASE_URL + name


def category(kind, caption):
    c = (caption or '').lower()
    if any(w in c for w in PIERCING_WORDS):
        return 'piercing'
    if any(w in c for w in TATTOO_WORDS):
        return 'tattoo'
    return 'life'


def load(path, default):
    try:
        with open(path) as f:
            return json.load(f)
    except (OSError, ValueError):
        return default


def dump(path, data):
    with open(path, 'w') as f:
        json.dump(data, f, ensure_ascii=False, indent=1)
        f.write('\n')


def save_review_thumb(it):
    img = best((it.get('image_versions2') or {}).get('candidates') or (((it.get('carousel_media') or [{}])[0]).get('image_versions2') or {}).get('candidates') or [])
    raw = fetch(img, {'User-Agent': UA}, tries=2) if img else None
    if raw:
        os.makedirs(REVIEW, exist_ok=True)
        im = Image.open(io.BytesIO(raw)).convert('RGB')
        im.thumbnail((240, 480))
        im.save(os.path.join(REVIEW, it['code'] + '.jpg'), 'JPEG', quality=70)


def convert_item(it, prev=None):
    """Личные посты (без слов про тату/пирсинг) скрыты и их медиа не скачиваются.
    Ручные пометки hidden/category/featured из прошлой версии сохраняются."""
    prev = prev or {}
    sc = it['code']
    mt = it.get('media_type')
    kind = 'carousel' if mt == 8 else ('reel' if mt == 2 else 'photo')
    caption = (it.get('caption') or {}).get('text') or ''
    cat = prev.get('category') or category(kind, caption)
    hidden = prev['hidden'] if 'hidden' in prev else cat == 'life'
    images, video = [], None
    if hidden:
        save_review_thumb(it)
    else:
        for i, m in enumerate(it.get('carousel_media') or [it]):
            img = best((m.get('image_versions2') or {}).get('candidates') or [])
            if img:
                p = save_image(img, f'ig/p/{sc}_{i}.webp')
                if p:
                    images.append(p)
            vid = best(m.get('video_versions') or [])
            if vid and i == 0 and kind == 'reel':
                video = save_video(vid, f'{sc}.mp4')
    return {
        'kind': kind,
        'shortcode': sc,
        'caption': caption,
        'views': it.get('play_count') or it.get('view_count') or 0,
        'likes': it.get('like_count') or 0,
        'comments': it.get('comment_count') or 0,
        'duration': round(it['video_duration']) if it.get('video_duration') else None,
        'taken_at': datetime.fromtimestamp(it['taken_at'], timezone.utc).strftime('%Y-%m-%d %H:%M:%S'),
        'cover': images[0] if images else None,
        'images': images if kind == 'carousel' else None,
        'video': video,
        'category': cat,
        'hidden': hidden,
        'featured': prev.get('featured', False),
        'pinned': bool(it.get('timeline_pinned_user_ids')),
    }


def main():
    data = load(DATA, {'profile': {}, 'posts': [], 'highlights': []})
    state = load(STATE, {'next_max_id': None, 'complete': False})
    by_code = {p['shortcode']: p for p in data['posts']}

    # Профиль
    prof = getjson(f'https://i.instagram.com/api/v1/users/web_profile_info/?username={USER}')
    if not prof:
        log('profile unavailable, stop')
        return 1
    u = prof['data']['user']
    uid = u['id']
    data['profile'] = {
        'username': u['username'],
        'full_name': u['full_name'],
        'bio': u['biography'],
        'external_url': u.get('external_url') or '',
        'bio_links': [l.get('url') for l in u.get('bio_links') or []],
        'category': u.get('category_name'),
        'followers': u['edge_followed_by']['count'],
        'following': u['edge_follow']['count'],
        'posts': u['edge_owner_to_timeline_media']['count'],
        'avatar': save_image(u['profile_pic_url_hd'], 'ig/avatar.webp', 640) or data['profile'].get('avatar', '/ig/avatar.jpg'),
        'collected_at': time.strftime('%Y-%m-%d'),
    }
    if os.path.exists(os.path.join(PUB, 'ig/avatar.webp')):
        data['profile']['avatar'] = '/ig/avatar.webp'
    log('profile', data['profile']['followers'], 'followers', data['profile']['posts'], 'posts')

    # Лента: сначала свежие (до первого уже известного), потом продолжаем старые с сохранённого курсора
    passes = [None] + ([state['next_max_id']] if state.get('next_max_id') and not state.get('complete') else [])
    for start in passes:
        max_id = start
        fresh_pass = start is None
        while time.time() < DEADLINE:
            url = f'https://i.instagram.com/api/v1/feed/user/{uid}/?count=33' + (f'&max_id={max_id}' if max_id else '')
            d = getjson(url)
            if not d or d.get('status') != 'ok':
                log('feed blocked, will resume next run')
                break
            known = 0
            for it in d.get('items', []):
                prev = by_code.get(it['code'])
                if prev and (prev.get('hidden') or (prev.get('cover') or '').startswith('/ig/p/')):
                    known += 1
                    continue
                by_code[it['code']] = convert_item(it, prev)
            data['posts'] = sorted(by_code.values(), key=lambda p: p['taken_at'], reverse=True)
            dump(DATA, data)
            log('posts', len(data['posts']))
            if fresh_pass and known and state.get('next_max_id') is not None:
                break  # дошли до уже скачанных, остальное продолжит второй проход
            max_id = d.get('next_max_id')
            if not d.get('more_available') or not max_id:
                if not fresh_pass or state.get('next_max_id') is None:
                    state['complete'] = True
                break
            if not fresh_pass or state.get('next_max_id') is None:
                state['next_max_id'] = max_id
            dump(STATE, state)
            time.sleep(random.uniform(4, 9))
    dump(STATE, state)

    # Актуальные и сторис — только с входом
    if SESSION:
        tray = getjson(f'https://i.instagram.com/api/v1/highlights/{uid}/highlights_tray/') or {}
        hls = []
        for t in tray.get('tray', []):
            hid = t['id']
            r = getjson(f'https://i.instagram.com/api/v1/feed/reels_media/?reel_ids={hid}') or {}
            items = (r.get('reels') or {}).get(hid, {}).get('items', [])
            slides = []
            for i, m in enumerate(items):
                key = hid.replace(':', '_')
                vid = best(m.get('video_versions') or [])
                img = best((m.get('image_versions2') or {}).get('candidates') or [])
                if vid:
                    v = save_video(vid, f'{key}_{i}.mp4')
                    if v:
                        slides.append({'type': 'video', 'src': v})
                elif img:
                    p = save_image(img, f'ig/h/{key}_{i}.webp')
                    if p:
                        slides.append({'type': 'image', 'src': p})
            cov = ((t.get('cover_media') or {}).get('cropped_image_version') or {}).get('url')
            hls.append({'title': t.get('title') or 'Актуальное', 'cover': save_image(cov, f'ig/h/{hid.replace(":", "_")}_cover.webp', 320) if cov else None, 'items': slides})
            time.sleep(random.uniform(2, 5))
        if hls:
            data['highlights'] = hls
        r = getjson(f'https://i.instagram.com/api/v1/feed/reels_media/?reel_ids={uid}') or {}
        story_items = (r.get('reels') or {}).get(uid, {}).get('items', [])
        if story_items:
            slides = []
            for m in story_items:
                vid = best(m.get('video_versions') or [])
                img = best((m.get('image_versions2') or {}).get('candidates') or [])
                if vid:
                    slides.append({'type': 'video', 'src': save_video(vid, f'story_{m["pk"]}.mp4')})
                elif img:
                    slides.append({'type': 'image', 'src': save_image(img, f'ig/s/{m["pk"]}.webp')})
            data['highlights'] = [{'title': 'Сторис', 'cover': data['profile']['avatar'], 'items': [s for s in slides if s['src']]}] + [h for h in data['highlights'] if h['title'] != 'Сторис']
        dump(DATA, data)
        log('highlights', len(data['highlights']))

    log('done: posts', len(data['posts']), 'of', data['profile']['posts'], 'complete' if state.get('complete') else 'partial')
    return 0


if __name__ == '__main__':
    sys.exit(main())
