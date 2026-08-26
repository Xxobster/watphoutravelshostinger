# Watphou Travels — Project Profile

Unknown fields remain `UNRESOLVED`.

## 1. Project identity

```yaml
project_name: Watphou Travels WordPress Rebuild
repository_root: D:\projects\watphoutravels
git_remote: git@github.com:Xxobster/watphoutravelsbluehost.git
legacy_wix_repo: git@github.com:Xxobster/watphoutravels.git
project_memory_directory: docs/project_memory
requirements_pdf: docs/Watphou_Travels_Website_Brief_Webmaster.pdf
master_prompt: docs/chatgpt prompt 4 cursor.txt
backup_zip: bkp/site files - 1657fdf4-40dc-40b1-99f2-ed231b1446ae.zip
current_live_site: https://www.watphou-travels.com/
design_reference: https://vietnamdiscovery.com/
production_host: Bluehost managed WordPress
production_deploy_status: NOT_STARTED
```

## 2. Demo environment (confirmed 2026-08-26)

```yaml
demo_vps_alias_ssh: sm
demo_vps_ip: 212.73.150.149
demo_vps_os: Ubuntu 22.04.5 LTS
demo_vps_ram_gb: 2.9
demo_vps_disk_free_gb: 5.5
demo_url: https://watphou.smbistro.duckdns.org
demo_web_root: /var/www/watphou-demo
demo_db_name: watphou_demo
demo_db_user: watphou_demo
demo_nginx_vhost: watphou-demo
demo_php_pool: watphou-demo
demo_unix_user: watphou
demo_basic_auth: ENABLED
demo_noindex: true
demo_payments: mock_only
```

## 3. Rejected demo host (recorded decision)

```yaml
rejected_host_ip: 185.203.119.52
rejected_reason: No nginx/PHP/MySQL; Node remoteandroid.service binds 80/443/8080/8443; only 4.8GB free
rejected_alternative_chosen: sm (212.73.150.149)
```

## 4. Existing site on demo VPS (must not break)

```yaml
existing_app_name: restaurant reservation system
existing_hostname: smbistro.duckdns.org
existing_nginx_site: smbistro
existing_backend: Node PM2 on 127.0.0.1:5000
existing_db: rtrs_db
existing_cert: smbistro.duckdns.org
```

## 5. Local development machine

```yaml
os: Windows 10
python: 3.12.10
git: installed
ssh: installed (OpenSSH)
scp: installed
tar: installed
php: NOT_INSTALLED
mysql: NOT_INSTALLED
node: NOT_INSTALLED
docker: NOT_INSTALLED
wp_cli: NOT_INSTALLED
development_mode: server_only
```

## 6. WordPress stack

```yaml
theme: watphou-travels (custom block theme)
plugin_core: watphou-core
plugin_bookings: watphou-bookings
mu_plugin: watphou-env.php
multilingual: Polylang Free
seo: Yoast SEO Free
page_builder: none
php_version_target: 8.1
mysql_version_demo: 8.0.46
```

## 7. Plugin allowlist (trusted only)

```yaml
allowed_plugins:
  - polylang
  - wordpress-seo
  - watphou-core
  - watphou-bookings
forbidden: nulled plugins, Elementor (unless documented exception)
```

## 8. Languages

```yaml
languages:
  - en (authoritative)
  - fr (import from backup PDFs where available)
  - th (professional translation required — drafts only)
url_structure: /en/ /fr/ /th/
```

## 9. Manager demo account

```yaml
wp_username: manager
wp_temp_password: "000000"  # demo only — never production
wp_role: tour_manager
outer_basic_auth: strong generated password — chat only, not in git
```

## 10. Open items

```yaml
real_tour_prices: UNRESOLVED
google_review_text: UNRESOLVED
petit_fute_reviews: UNRESOLVED
verified_since_2008: UNRESOLVED
smtp_demo: UNRESOLVED
bcel_merchant_contract: UNRESOLVED
bluehost_ssh_access: UNRESOLVED
```
