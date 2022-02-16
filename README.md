# netlify-cms-oauth-provider-php

***External authentication providers were enabled in netlify-cms version 0.4.3. Check your web console to see your netlify-cms version.***

[netlify-cms](https://www.netlifycms.org/) has its own github OAuth client. This implementation was created by reverse engineering the results of that client, so it's not necessary to reimplement client part of [netlify-cms](https://www.netlifycms.org/).

Github, Github Enterprise and Gitlab are currently supported, but as this is a general Oauth client, feel free to submit a PR to add other git hosting providers.

Other implementations in: 
* [Node](https://github.com/vencax/netlify-cms-github-oauth-provider) - which was the main inspiration for this
* [PHP](https://github.com/TSV-Zorneding-1920/netlify-cms-oauth-provider-php) that did not work for me...
* [Go lang](https://github.com/igk1972/netlify-cms-oauth-provider-go).

## 1) Install Locally

**Install Repo Locally**

```bash
git clone https://github.com/mcdeck/netlify-cms-oauth-provider-php
cd netlify-cms-oauth-provider-php
composer install
```

**Create Oauth App**
Information is available on the [Github Developer Documentation](https://developer.github.com/apps/building-integrations/setting-up-and-registering-oauth-apps/registering-oauth-apps/) or [Gitlab Docs](https://docs.gitlab.com/ee/integration/oauth_provider.html). Fill out the fields however you like, except for **authorization callback URL**. This is where Github or Gitlab will send your callback after a user has authenticated, and should be `https://your.server.com/callback` for use with this repo.

## 2) Config

### Auth Provider Config

Configuration is done with environment variables, which can be supplied as command line arguments, added in your app hosting interface, or loaded from a .env ([symfony env files](https://symfony.com/doc/current/configuration.html#configuration-environments)) file.

**Example .env.local file:**

```ini
# Default values for GitHub - leave as they are
# OAUTH_PROVIDER=github
# SCOPES=api,user,repo
# overwrite for GitHub Enterprise
# OAUTH_DOMAIN=https://github.com
OAUTH_CLIENT_ID=11111111111111
OAUTH_CLIENT_SECRET=22222222222222222222222222222222222
REDIRECT_URI=https://auth.example.com/callback/
ORIGIN=example.com

# Set to production environment when deploying
APP_ENV=prod
```

For Gitlab you also have to provide these environment variables:
```ini
# You can customize this to your URL for self-hosted GitLab instances
OAUTH_DOMAIN=https://gitlab.com
OAUTH_PROVIDER=gitlab
SCOPES=api
```

**Client ID & Client Secret:**
After registering your Oauth app, you will be able to get your client id and client secret on the next page.

**Redirect URL (optional in github, mandatory in gitlab):**
Include this if you need your callback to be different from what is supplied in your Oauth app configuration.

**Git Hostname (Default github.com):**
This is only necessary for use with Github Enterprise or Gitlab.

### CMS Config
You also need to add `base_url` to the backend section of your netlify-cms's config file. `base_url` is the live URL of this repo with no trailing slashes.

```yaml
backend:
  name: [github | gitlab]
  repo: user/repo   # Path to your Github/Gitlab repository
  branch: master    # Branch to update
  base_url: https://auth.example.com # Path to ext auth provider
```

## 3) Deploy

### FTP

Create an `.env.local` file next to `.env` and set `CLIENT_ID`, `CLIENT_SECRET` and `REDIRECT_URL` as per the example above.

Upload to everyhting and point your webserver to `public` folder, or chose whatever method you normally chose to deploy Symfony apps.
