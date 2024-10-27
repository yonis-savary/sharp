[< Back to Sharp README](../README.md)

# 📚 Sharp-PHP Documentation

The goals behind Sharp are :
1. Write some intuitive clean code
2. Remove unnecessary abstraction layers
3. Don't hide the code behind Facades objects
4. Make a framework that doesn't break your IDE
5. Let you code apps quickly without worrying about setup/build
6. Have as few dependencies as possible (So far, only [PHPUnit](https://phpunit.de/) and [`symfony/process`](https://symfony.com/doc/current/components/process.html) are required)
7. Automate tedious task (like model creation)

You can browse this documentation with an IDE like Visual Studio Code with the markdown preview,
or in GitHub directly

## Tutorials

- [✅ Getting started](./tutorials/getting-started.md)


## Summary

Even though this documentation should be enough to get started, don't hesitate to checkout the framework source code, it is a good way to learn about it too

Core
- [📦 Setup & Configuration](./core/config.md)
- [🧩 Understanding Sharp components](./core/components.md)
- [⚗️ Utils](./core/utils.md)
- [💻 CLI command](./core/commands.md)
- [📃 Logging](./core/logging.md)
- [📁 Storage](./env/storage.md)
- [🔏 Session](./core/session.md)
- [🔥 Cache](./env/cache.md)
- [🪝 Events](./core/events.md)
- [🚃 ObjectArray class](./core/object-array.md)

Routing & Logic
- [🛣️ Routing](./logic/routing.md)
- [⚙️ Controllers](./logic/controllers.md)
- [🚦 Middlewares](./logic/middlewares.md)
- [📨 Requests & Responses](./logic/requests-and-responses.md)
- [🖌️ View Rendering](./logic/rendering.md)


Database
- [📚 Database](./data/database.md)
- [⏫ Migration](./data/migration.md)
- [📖 Model & Queries](./data/model-query.md)

Business Logic
- [🥤 Session Straw](./extras/session-straw.md)
- [🗂 App Utils (AppStorage, AppCache...)](./extras/app-utils.md)

Extras
- [🎨 Serving assets](./extras/assets.md)
- [🚘 Automatic CRUD API](./extras/autobahn.md)
- [🔐 Authentication](./security/authentication.md)
- [✅ CSRF](./security/csrf.md)
- [🕒 Scheduler](./extras/scheduler.md)
- [⌛️ QueueHandler Trait](./extras/queue-handler.md)
- [🌐 Code Helpers](./core/helpers.md)
- [💬 Q/A & Snippets](./extras/snippets.md)

Miscellaneous
- [✅ Testing the framework/apps](./misc/testing.md)

