# Sharp PHP

> [!IMPORTANT]
> This project is still under development

Sharp is a Framework for PHP 8 that focuses on code cleanliness and simplicity

The goal is to have a good balance between abstraction and concrete objects

## 📚 Documentation and Tutorials

You can find resources to work/learn with Sharp in the [Docs directory](./docs/README.md)

## 📦 Get Sharp

```bash
# Add Sharp to your project
composer require yonis-savary/sharp

# Copy Public directory and 'do' script...
# ...on linux
cp -r vendor/yonis-savary/sharp/src/Core/Server/* .
# ...on windows
xcopy /s vendor/yonis-savary/sharp/src/Core/Server/* .

# Create an empty configuration/project
php do fill-configuration
php do create-application MyProject

# Launch built-in web server
php do serve
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
- 🟢 Configuration (JSON Format)
- 🟢 Caching
- 🟢 Logging
- 🟢 Events
- 🟢 CLI Commands (With base utilities commands)
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
- 🟢 Models
- 🟢 FTP Directory
- 🟢 Queues support
- 🟢 Array object (for functional programming)

🔐 Security
- 🟢 Authentication
- 🟢 CSRF

🚀 Extras
- 🟢 Asset serving
- 🟢 Automatic CRUD API for Models
- 🔵 Scheduler System

...and more ! The [`SharpExtension`](https://github.com/yonis-savary/sharp-extensions) repository got some additionnal features that can be used to make development faster