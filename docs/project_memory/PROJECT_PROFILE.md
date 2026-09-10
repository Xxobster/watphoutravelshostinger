# Watphou Travels — Project Profile

Unknown fields remain `UNRESOLVED`.

## 1. Project identity

```yaml
project_name: Watphou Travels WordPress Rebuild
repository_root: D:\projects\watphoutravels
git_remote: git@github.com:Xxobster/watphoutravelshostinger.git
legacy_wix_repo: git@github.com:Xxobster/watphoutravels.git
project_memory_directory: docs/project_memory
requirements_pdf: docs/Watphou_Travels_Website_Brief_Webmaster.pdf
master_prompt: docs/chatgpt prompt 4 cursor.txt
backup_zip: bkp/site files - 1657fdf4-40dc-40b1-99f2-ed231b1446ae.zip
current_live_site: https://www.watphou-travels.com/
design_reference: https://vietnamdiscovery.com/
production_host: Hostinger (temporary domain until real domain is attached)
production_deploy_status: STAGING_ON_HOSTINGER
```

## 2. Staging environment (Hostinger — current as of 2026-09-10)

```yaml
staging_url: https://darkslategray-snake-182151.hostingersite.com
staging_host: Hostinger
staging_hostinger_username: u916301613
staging_wordpress_title: Watphou Travels
staging_wp_login: wptadmin
staging_noindex: true
staging_payments: mock_only
```

## 2b. Retired demo environment (removed from VPS sm on 2026-09-10)

```yaml
status: RETIRED
demo_vps_alias_ssh: sm
demo_vps_ip: 212.73.150.149
former_demo_url: https://watphou.smbistro.duckdns.org
former_demo_web_root: /var/www/watphou-demo
former_demo_db_name: watphou_demo
offline_backup_on_vps: /var/backups/watphou-demo-final-20260910_142038/
deprovision_script: scripts/deprovision_demo_vps.sh
```

## 3. Rejected demo host (recorded decision)

```yaml
rejected_host_ip: 185.203.119.52
rejected_reason: No nginx/PHP/MySQL; Node remoteandroid.service binds 80/443/8080/8443; only 4.8GB free
rejected_alternative_chosen: sm (212.73.150.149)
```

## 4. Other sites on VPS sm (must not break)

```yaml
rule: Never modify smbistro, cirlapp, or cirl-ip on VPS sm
existing_app_name: restaurant reservation system
existing_hostname: smbistro.duckdns.org
existing_nginx_site: smbistro
note: DuckDNS for smbistro.duckdns.org did not resolve from the VPS on 2026-09-10; nginx vhost was left unchanged
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
bluehost_ssh_access: SUPERSEDED_BY_HOSTINGER
hostinger_temporary_domain: darkslategray-snake-182151.hostingersite.com
final_production_domain: UNRESOLVED
```
