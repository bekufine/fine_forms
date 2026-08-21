# さくらのVPS（Rocky Linux 10 x86_64）デプロイ手順書

このアプリ（Laravel 13 + Vue 3 / Vite）を、さくらのVPS の Rocky Linux 10 上で
公開するための手順です。**上から順にコピペ**していけば動きます。

- 構成: **Nginx + PHP-FPM(PHP 8.4) + SQLite**
- SQLite を使うので DB サーバーの構築は不要（小～中規模フォーム用途で十分）
- MySQL を使いたい場合は末尾の「付録A」を参照

> 前提: さくらのVPS のコントロールパネルで OS を「Rocky Linux 10 x86_64」で
> インストール済み。SSH で `root` もしくは sudo 可能なユーザーでログインできる状態。

---

## 0. このマニュアルで使う値（先に決めておく）

| 項目 | 例 | あなたの値 |
|------|-----|-----------|
| ドメイン or IP | `forms.example.com` / `160.16.xxx.xxx` | |
| 公開ディレクトリ | `/var/www/fine_forms` | |
| 実行ユーザー | `nginx` | |

以降の手順で `forms.example.com` の部分は自分のドメイン（無ければVPSのIP）に読み替えてください。

---

## 1. サーバーの初期設定

SSH でログイン後、まずシステムを最新化します。

```bash
sudo dnf update -y
sudo dnf install -y git unzip tar policycoreutils-python-utils acl
```

### ファイアウォール（HTTP/HTTPSを開放）

```bash
sudo systemctl enable --now firewalld
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

> さくらのVPS側の「パケットフィルタ」機能を使っている場合は、コントロールパネルで
> 80/443（Web）と 22（SSH）が許可されているか確認してください。

---

## 2. PHP 8.4 のインストール（Remiリポジトリ）

Laravel 13 は PHP 8.3 以上が必要です。確実に新しい版を入れるため Remi を使います。

```bash
# EPEL と Remi リポジトリを追加
sudo dnf install -y https://dl.fedoraproject.org/pub/epel/epel-release-latest-10.noarch.rpm
sudo dnf install -y https://rpms.remirepo.net/enterprise/remi-release-10.rpm

# PHP 8.4 と必要な拡張をインストール
sudo dnf install -y --enablerepo=remi \
  php84-php-fpm php84-php-cli \
  php84-php-mbstring php84-php-xml php84-php-bcmath \
  php84-php-curl php84-php-gd php84-php-zip php84-php-intl \
  php84-php-pdo php84-php-sqlite3 php84-php-opcache php84-php-fileinfo
```

Remi の `php84` は `php84` というコマンド名になります。使いやすいように
標準の `php` として使えるようにします。

```bash
# php84 を標準の php コマンドにする（scl 経由）
sudo ln -sf /opt/remi/php84/root/usr/bin/php /usr/local/bin/php
php -v   # PHP 8.4.x と表示されればOK
```

---

## 3. Composer のインストール

```bash
cd /tmp
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm -f composer-setup.php
composer --version   # 2.x が表示されればOK
```

---

## 4. Node.js 22 のインストール（Viteビルド用）

Vite 8 は Node.js 20.19+ / 22 系が必要です。

```bash
curl -fsSL https://rpm.nodesource.com/setup_22.x | sudo bash -
sudo dnf install -y nodejs
node -v   # v22.x と表示されればOK
```

---

## 5. アプリを配置する

このプロジェクト一式をサーバーの `/var/www/fine_forms` に置きます。

**方法A: Git で持ってくる場合（リポジトリがある場合）**

```bash
sudo mkdir -p /var/www
sudo git clone <あなたのリポジトリURL> /var/www/fine_forms
```

**方法B: 手元のZIPをアップロードする場合**

手元のPCから（PowerShell 等で）scp でアップロードし、展開します。

```powershell
# 手元PC側で実行（例）
scp fine_forms.zip ユーザー名@forms.example.com:/tmp/
```
```bash
# サーバー側で実行
sudo mkdir -p /var/www/fine_forms
sudo unzip /tmp/fine_forms.zip -d /var/www/fine_forms
# ZIPの中に一段フォルダがある場合は中身を上げてください
```

**方法A・Bどちらの場合も、配置直後に所有権を作業ユーザー(rocky)に移します。**
`sudo git clone` / `sudo unzip` 直後はファイルが `root` 所有のままで、
次の手順6〜7（composer / npm / artisan）が Permission denied で失敗するためです。

```bash
# 以降の作業を rocky で行えるように所有権を取得（rocky は実際の作業ユーザー名に）
sudo chown -R rocky:rocky /var/www/fine_forms
```

> グループを `nginx` にする最終的な権限設定は、DBやビルド成果物が揃う **手順8** で行います。
> ここではまず「rocky が作業できる」状態にするだけでOKです。

---

## 6. 依存パッケージのインストールとビルド

```bash
cd /var/www/fine_forms

# PHP依存（本番用: 開発用パッケージを除外して軽量化）
composer install --no-dev --optimize-autoloader

# フロントエンド（Vue/Vite）のビルド
npm ci          # package-lock.json 通りに再現インストール（install より速く確実）
npm run build
```

> 初回で `node_modules` が別ユーザー所有になって壊れた場合は、
> `rm -rf node_modules && npm ci` でクリーンに入れ直せます。

> `npm run build` で `public/build/` にビルド済みアセットが生成されます。

---

## 7. 環境設定（.env）

```bash
cd /var/www/fine_forms
cp .env.example .env

# SQLite のデータベースファイルを作成
touch database/database.sqlite

# アプリキーを生成
php artisan key:generate
```

`.env` を編集して本番向けに設定します。

```bash
nano .env
```

以下のように変更してください（**太字の項目が変更点**）：

```dotenv
APP_NAME=FineForms
APP_ENV=production
APP_DEBUG=false
APP_URL=https://forms.example.com     # ← 自分のドメイン(またはhttp://IP)

DB_CONNECTION=sqlite
# SQLite利用時は DB_HOST 等は不要（コメントのままでOK）
```

保存したら DB を初期化（テーブル作成）し、初期フォーム（アンケート）を投入します。

```bash
php artisan migrate --force
php artisan db:seed --force
```

> **`db:seed` は必ず実行してください。** フォームのタイトル・設問・選択肢は
> DB に保存されており、これを投入するのが seeder です。実行しないとフォームが
> 1件も存在しません。seeder は `updateOrCreate` で冪等なので、再実行しても
> 既存の回答データは壊れず、コード側で変更した文言だけが DB に反映されます。

Laravel の最適化キャッシュを作成します（本番高速化）。

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 8. ファイル権限と SELinux 設定（重要）

このアプリには **2種類のユーザー** が関わります。両者が所有権を奪い合わないように
設定するのが最大のポイントです（ここを間違えると再デプロイのたびに権限エラーになります）。

| 役割 | ユーザー | 何をする |
|------|---------|---------|
| デプロイ作業者 | `rocky`（sudo可のログインユーザー） | `git pull` / `composer` / `npm` / `php artisan` |
| Web・キュー実行 | `nginx`（PHP-FPM / queue worker） | 実行時に `storage` / `database` へ書き込み |

> **絶対に `sudo chown -R nginx:nginx` で全部を nginx 所有にしないこと。**
> それをやると次の `git pull` や `composer`・`npm` が Permission denied で失敗します。
> 正解は「所有者 `rocky` ＋ グループ `nginx`」の共有構成です。

```bash
# デプロイユーザー(rocky)を nginx グループに追加（グループ経由で相互に書けるように）
sudo usermod -aG nginx rocky
# ↑ 反映のため、この後 一度ログアウト→再ログイン するか `newgrp nginx` を実行

cd /var/www/fine_forms

# 所有者=rocky（デプロイ用）／ グループ=nginx（実行用）
sudo chown -R rocky:nginx /var/www/fine_forms

# ベースの権限（ディレクトリ755・ファイル644・artisanは実行可）
sudo find /var/www/fine_forms -type d -exec chmod 755 {} \;
sudo find /var/www/fine_forms -type f -exec chmod 644 {} \;
sudo chmod +x artisan

# 実行時に書き込むディレクトリだけ「グループ書き込み可 + setgid」に
#  setgid(2) を付けると、新規作成されるファイルも自動で nginx グループになる
sudo chmod -R 2775 storage bootstrap/cache database
sudo chmod 664 database/database.sqlite

# デフォルトACL: これ以降 どちらのユーザーが作ったファイルも rocky/nginx 両方が書けるようにする
#  （これが chown の往復を不要にする決め手。acl が無ければ: sudo dnf install -y acl）
sudo setfacl -R    -m u:rocky:rwX -m u:nginx:rwX storage bootstrap/cache database
sudo setfacl -R -d -m u:rocky:rwX -m u:nginx:rwX storage bootstrap/cache database
```

これで:
- `rocky` は所有者なので `git pull` / `composer` / `npm` / `artisan` が通る
- `nginx` はグループ＋ACLで `storage`・`database` に書ける
- 以後 **`chown` を毎回やり直す必要が無い**

### SELinux（Rocky 10 は既定で有効）

SELinux を有効なまま正しく動かす設定です（無効化は非推奨）。
ここで設定する `semanage fcontext` のルールは**永続的**なので、初回に一度だけ実行すればOKです
（再デプロイ時は `restorecon` だけで足ります）。

```bash
# storage / bootstrap/cache / sqlite に書き込み許可を付与
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/fine_forms/storage(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/fine_forms/bootstrap/cache(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/fine_forms/database(/.*)?"

# 読み取り用に全体のコンテキストも設定
sudo semanage fcontext -a -t httpd_sys_content_t "/var/www/fine_forms(/.*)?"

# 上記ルールをファイルに適用
sudo restorecon -Rv /var/www/fine_forms
```

> ※ SELinux コンテキストは**所有権とは独立**です。`chown` で壊れることはありません。
> 新規ファイルは親ディレクトリのコンテキストを自動継承するので、再デプロイ時は
> 気になったときだけ `sudo restorecon -Rv storage bootstrap/cache database` を実行すれば十分です。

---

## 9. PHP-FPM の設定

Remi の PHP-FPM を nginx ユーザーで動かすようにします。

```bash
sudo nano /etc/opt/remi/php84/php-fpm.d/www.conf
```

以下の行を確認・変更します（既定が `apache` の場合は `nginx` に）：

```ini
user = nginx
group = nginx
listen = /run/php-fpm/php84-fpm.sock
listen.owner = nginx
listen.group = nginx
listen.mode = 0660
```

起動して自動起動を有効化します。

```bash
sudo systemctl enable --now php84-php-fpm
sudo systemctl status php84-php-fpm --no-pager
```

---

## 10. Nginx のインストールと設定

```bash
sudo dnf install -y nginx
```

サイト設定ファイルを作成します。

```bash
sudo nano /etc/nginx/conf.d/fine_forms.conf
```

以下を貼り付けます（`server_name` を自分のドメイン/IPに変更）：

```nginx
server {
    listen 80;
    server_name 133.242.173.231;          # ← ドメイン or IP

    root /var/www/fine_forms/public;         # ← publicがドキュメントルート
    index index.php;

    charset utf-8;
    client_max_body_size 20M;                # アップロード上限（必要に応じ調整）

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Vite のビルド済みアセットは内容ハッシュ付きファイル名なので、
    # 中身が変わればファイル名も変わる。よって無限キャッシュして安全。
    # （HTML 側は Laravel の NoStoreHtml ミドルウェアで no-store を付与しており、
    #   常に最新のアセット名を読み込むため、古い文言が表示され続けない）
    location /build/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php-fpm/php84-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

設定をテストして起動します。

```bash
sudo nginx -t                       # syntax is ok と出ればOK
sudo systemctl enable --now nginx
```

---

## 11. キュー（Queue）の常駐設定

このアプリは `QUEUE_CONNECTION=database` を使います。メール送信やバックグラウンド
処理を確実に動かすため、キューワーカーを systemd で常駐させます。

```bash
sudo nano /etc/systemd/system/fine_forms-queue.service
```

```ini
[Unit]
Description=Fine Forms Queue Worker
After=network.target

[Service]
User=nginx
Group=nginx
Restart=always
WorkingDirectory=/var/www/fine_forms
ExecStart=/usr/local/bin/php /var/www/fine_forms/artisan queue:work --sleep=3 --tries=3 --timeout=90

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now fine_forms-queue
sudo systemctl status fine_forms-queue --no-pager
```

---

## 12. 動作確認

ブラウザで `http://forms.example.com`（またはVPSのIP）にアクセスして、
アプリの画面が表示されれば成功です。

うまくいかない場合は「トラブルシューティング」を参照してください。

---

## 13. HTTPS化（Let's Encrypt・任意だが推奨）

ドメインをVPSのIPに向けている場合、無料でHTTPS化できます。

```bash
sudo dnf install -y certbot python3-certbot-nginx
sudo certbot --nginx -d forms.example.com
```
sudo certbot --nginx -d ik1-123-68477.vs.sakura.ne.jp

案内に従ってメールアドレス入力・規約同意すると、自動でHTTPS設定＋証明書取得されます。
自動更新の確認：

```bash
sudo systemctl status certbot-renew.timer --no-pager 2>/dev/null \
  || sudo systemctl list-timers | grep certbot
```

HTTPS化したら `.env` の `APP_URL` を `https://...` に更新し、
`php artisan config:cache` を再実行してください。

---

## 14. 更新（再デプロイ）手順

コードを更新したときは以下を実行します。
**すべて `rocky` ユーザーで実行してください**（手順8で権限を正しく設定していれば
`sudo chown` の往復は不要です）。

```bash
cd /var/www/fine_forms

# コード更新（Gitの場合）
git pull

composer install --no-dev --optimize-autoloader
npm ci          # ← lockファイル通りに再現インストール（install より速く確実）
npm run build

php artisan migrate --force
php artisan db:seed --force   # ← フォームの文言変更を本番DBに反映（冪等・回答データは保持）

# .env に新しい項目が増えていないか確認（増えていれば .env に追記）
diff <(grep -oP '^[A-Z_]+(?==)' .env.example | sort) \
     <(grep -oP '^[A-Z_]+(?==)' .env | sort) | grep '^<' || echo "新規キーなし"

# キャッシュ作り直し
php artisan config:cache
php artisan route:cache
php artisan view:cache

# キューワーカー再起動（新しいコードを読ませる）
sudo systemctl restart fine_forms-queue
```

> **重要: 再デプロイで `sudo chown -R nginx:nginx` は実行しないでください。**
> これをやると次回の `git pull` / `composer` / `npm` が Permission denied になります。
> 手順8の「所有者 rocky ＋ グループ nginx ＋ setgid ＋ ACL」構成なら、
> 新規ファイルも自動で両ユーザーが書けるため、権限の再設定は不要です。
>
> もし手順8より前のマニュアルで一度 `nginx:nginx` にしてしまった場合は、
> 一度だけ手順8を最初から実行し直せば正常な状態に戻ります。

---

## 15. トラブルシューティング

### 500 エラー / 真っ白な画面

```bash
# Laravelのログを確認
sudo tail -n 50 /var/www/fine_forms/storage/logs/laravel.log
# Nginx / PHP-FPM のログ
sudo tail -n 50 /var/log/nginx/error.log
sudo journalctl -u php84-php-fpm -n 50 --no-pager
```

よくある原因：
- **権限エラー（Permission denied）** → 手順8を再実行（chown + chmod + SELinux）
- **SELinux が原因** → `sudo ausearch -m avc -ts recent` で拒否ログ確認。
  一時的に切り分けるなら `sudo setenforce 0`（動けばSELinux設定漏れ。切り分け後は必ず `sudo setenforce 1` に戻す）
- **APP_KEY 未設定** → `php artisan key:generate` を実行

### CSS/JSが当たらない（デザインが崩れる）

`npm run build` が未実行、または `public/build/` が無い。
手順6の `npm run build` を再実行してください。

### 画面は出るがデータが保存されない

`database/database.sqlite` の権限/SELinux。手順8を再確認。
`php artisan migrate --force` が実行済みかも確認。

### デプロイ中に Permission denied 連発（`git pull` / `composer` / `npm` / `db:seed`）

以下のようなエラーが出るのは、**ファイルが `nginx` 所有になっていて作業ユーザー `rocky` が書けない**のが原因です。

- `error: cannot open '.git/FETCH_HEAD': Permission denied`（git pull）
- `laravel.log could not be opened` / `bootstrap/cache directory must be present and writable`（composer / artisan）
- `EACCES: permission denied, mkdir '.../node_modules/...'`（npm）
- `attempt to write a readonly database`（db:seed / SQLite）

**やってはいけない対処**: `chown rocky` ↔ `chown nginx` を毎回切り替えること（堂々巡りになります）。

**正しい対処**: 手順8を最初から一度実行し、「所有者 rocky ＋ グループ nginx ＋ setgid ＋ ACL」の
状態にすればOK。以後 rocky も nginx も両方書けるので、切り替えは不要になります。

```bash
# 現在の所有者を確認（storage/database が nginx 単独所有ならこれが原因）
ls -ld storage bootstrap/cache database database/database.sqlite
```

### 502 Bad Gateway

PHP-FPM が起動していない or ソケットのパスが不一致。
`sudo systemctl status php84-php-fpm` と、手順9・10のソケットパス
（`/run/php-fpm/php84-fpm.sock`）が一致しているか確認。

---

## 付録A. SQLite ではなく MySQL/MariaDB を使う場合

```bash
sudo dnf install -y mariadb-server php84-php-mysqlnd
sudo systemctl enable --now mariadb
sudo mysql_secure_installation   # 対話で初期設定

# DBとユーザー作成
sudo mysql -u root -p
```
```sql
CREATE DATABASE fine_forms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'forms'@'localhost' IDENTIFIED BY '任意の強いパスワード';
GRANT ALL PRIVILEGES ON fine_forms.* TO 'forms'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

`.env` を以下に変更：

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fine_forms
DB_USERNAME=forms
DB_PASSWORD=任意の強いパスワード
```

その後 `php artisan migrate --force` を実行。SQLite用の
`database/database.sqlite` は不要になります。

---

以上で公開完了です。何か詰まったら手順15を確認してください。
