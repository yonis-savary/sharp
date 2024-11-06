# Sharp PHP

## Welcome !

Sharp is a framework for PHP 8 that focuses on code cleanliness and simplicity, the goal is to have a good balance between abstraction and concrete objects, making a framework that can work with your IDE and doesn't use magic syntaxes or unnecessary complexity layers.

## 📚 Documentation and Tutorials

You can find resources to work/learn with Sharp in the [`docs/` directory](./docs/README.md)

## 📦 Get Sharp

```bash
# Add Sharp to your project
composer require yonis-savary/sharp

# Copy Public directory and 'do' script, .gitignore, etc.
# Then initialize your Sharp project
composer exec sharp-install && php do initialize

# Launch built-in web server
php do serve
# specify port
php do serve --port=8080
```

Your directory will look like
- `.git/`
- `MyProject/`
- `Public/`
- `vendor/`
- `.gitignore`
- `composer.json`
- `composer.lock`
- `do`
- `sharp.json`

## Release's features

- 🟢 - tested feature
- 🔵 - tested feature (some edge-case tests may be missing)
- 🟡 - untested feature

🫀 Core
- 🟢 Configuration (sharp.json)
- 🟢 Caching
- 🟢 Logging
- 🟢 Events
- 🟢 CLI Commands (With base utilities commands)
- 🔵 CLI build system
- 🟢 Tests

🌐 Web
- 🟢 Session
- 🟢 Request / Responses
- 🔵 Request Fetch (CURL)
- 🟢 Controllers
- 🔵 Renderer
- 🟢 Routing / Middlewares

📁 Data
- 🟢 Database (With SQLite support)
- 🔵 Simple migration system
- 🟢 Models
- 🟢 FTP directory support
- 🟢 Queues support
- 🟢 Array object (for functional programming)

🔐 Security
- 🟢 Authentication
- 🟢 CSRF

🚀 Extras
- 🟢 Simple assets serving
- 🟢 Automatic CRUD API for your models
- 🟢 Scheduler system

...and more ! The [`SharpExtension`](https://github.com/yonis-savary/sharp-extensions) repository got some additionnal features that can be used to make development faster

# Next release objectives

- [x] Scheduler system
- [x] New request validation system
- [x] Command rework
- [x] Framework installation rework
- [ ] Test every framework commands
- [ ] Test app creation/integration
- [ ] Test caching & benchmark performances
