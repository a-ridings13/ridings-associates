# Ridings & Associates LLC
## What is this?

This is a starting point for Ridings & Associates LLC web application forked from siteworx pro slim v4 boilerplate.

## What it's not

This is not a new frame work or ment to be a one size fits all solution.  Most of this application is specificly built for what I need to get a new project up off the ground. 

*Why not use Laravel, Cake or Symphony?*
I like laravel/lumin and have used it in several projects.  Cake and the others are also fine but I still like the basics of slim. What it does what it needs to do and nothing else. 

I have always found it more than I have always needed.  I don't really need everything plus the kitchen sink in my projects to start. 

*No CSS?*
No, most client are going to bring their own styles or templates.  Check these projects out as a great place to start

- [Vuetifyjs](https://vuetifyjs.com/en/)
- [Prime Vue](https://www.primefaces.org/primevue/)
- [Vue Bootstrap](https://bootstrap-vue.js.org/)
- [Vue Material](https://vuematerial.io/)

### Vagrant Development
Vagrant is used for local development and includes xdebug.  

```vagrant up``` Will start the vagrant server

Add ```192.168.33.10 vagrant.local``` to your hosts file

### NPM and VueJs

This boiler plate is bundled with VueJS as it's javascript framework

#### install
``npm install`` install node dependencies 

``npm run watch`` Build and watch for file changes

``npm run development`` Build with dev dependencies.  This mode also puts vue into development mode
making Vue dev tools available.

``npm run production`` Build for production. Minify files and put vue into production mode.

### Slim PHP

Slim is the base framework I use for all my projects.  It's simple and to the point.  It does what it needs to do and just that. 

More information can be found [here](http://www.slimframework.com/) about the slim php framework slim php.  Routes are registered in Library\App.  Controllers must extend the 
abstract base controller

### Twig

[Twig](https://twig.symfony.com/) is my templating engine of choice.  Again, no reason to re-invent the wheel. 

### Docker 

Ready to build a docker container out of the box.
If you need any customizations refer to the DockerFile and /bin/EntryPoint.sh

`docker build .` Will start the docker build process

## OAuth API Authentication

The api supports the following authentication schema.

* `client_credentials` Server -> Server
* `authorization_code` Without PKCE Trusted Person -> Server
* `authorization_code` With PKCE External Person -> Server

### Client Credentials

Server -> Server. Simplest flow. Should only be used with internal trusted code.

`POST` `/api/oauth/access_token`

```json
{
  "grant_type": "client_credentials",
  "client_id": "ffevz...4dkvd",
  "client_secret": "nnd4...4snaq",
  "scope": "user:read user:write company:read"
}
```

```json
{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "eyJ0eXAiOi...2DKg"
}
```

### Authorization Code Without PKCE
Use the ` --generate-oauth-client` command. When asked if the client is 'internal' enter 'y'.

Use the ` --add-client-redirect-uri` command. Add a valid url.

Redirect the user to 

`GET` `/api/oauth/authorize?response_type=code&state=[csrf_token]&client_id=[clientID]&scope=[Scopes]&redirect_uri=[Redirect uri]`

Don't provide any scopes if you don't want your server to support scopes.

This login page can be customized in the view. It must post to `/api/oauth/authorize` 
which will return a response with a 302 to the redirect uri on successful login.

The user will be redirected back to your redirect uri.
`[your-redirect-uri]?code=[authorization_code]&state=[csrf_token]`

You can then exchange the code for an access token

`POST` `/api/oauth/access_token`
```json
{
  "grant_type": "authorization_code",
  "client_id": "GsWBX...iv9o5",
  "client_secret": "6Neg2m3...EULnowZT0uJh",
  "code": "def5020023c2fb729...3e9",
  "redirect_uri": "[your-redirect-uri]"
}
```

this time a refresh token will also be given
```json
{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "eyJ0e...djYcng",
  "refresh_token": "def5...049b"
}
```

### With PKCE

Use the ` --generate-oauth-client` command. When asked if the client is 'internal' enter 'n'.

Same as without but you must include a code_challenge. the challenge must pass a `[A-Za-z0-9-._~]{43,128}` regex.

you can the specify a `code_challenge_method` of `plain` or `S256`. `plain` is used by default.

`GET` `/api/oauth/authorize?response_type=code&state=[csrf_token]&client_id=[clientID]&scope=[Scopes]&redirect_uri=[Redirect uri]&code_challenge=[your-code]`

This time when you exchange the code you must also provide the code_verifier

```json
{
  "grant_type": "authorization_code",
  "client_id": "GsWB...9o5",
  "client_secret": "6Neg...wZT0uJh",
  "code": "def502002d2dbb4...f78e9bc",
  "redirect_uri": "[your-redirect-uri]",
  "code_verifier": "L3Ua...IQ6hK"
}
```
