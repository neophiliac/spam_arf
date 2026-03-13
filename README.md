# spam_arf
Send the current email in ARF format

## Installation

### Requirements

- Roundcube 1.5 or later
- PHP mail delivery configured and working in Roundcube

### Via Composer (recommended)

From your Roundcube root directory:

```bash
composer require merlot/spam_arf
```

This will install the plugin into `plugins/spam_arf/` and register it automatically.

### Manual installation

1. Copy (or clone) this repository into your Roundcube plugins directory:

   ```bash
   git clone https://github.com/neophiliac/spam_arf plugins/spam_arf
   ```

2. Open `config/config.inc.php` (or `config/main.inc.php` on older installs) and add `spam_arf` to the plugins array:

   ```php
   $config['plugins'] = ['spam_arf'];
   ```

### Skin support

The plugin includes stylesheets for the `elastic` and `classic` Roundcube skins. No additional steps are needed for these skins. If you use a third-party skin, copy one of the existing skin directories as a starting point and place it at `plugins/spam_arf/skins/<skin-name>/`.

