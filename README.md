# Sharp PHP

## Welcome !

Sharp is a framework for PHP 8 that focuses on code cleanliness and simplicity, the goal is to have a good balance between abstraction and concrete objects, make a framework that just work with your IDE and doesn't use some magic syntax or unecessary complexity.

## 📚 Documentation and Tutorials

You can find resources to work/learn with Sharp in the [docs/ directory](./docs/README.md)

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
php do create-configuration
php do create-application MyProject

# Launch built-in web server
php do serve
# specify port
php do serve 8080
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
- 🔵 Simple migration system
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