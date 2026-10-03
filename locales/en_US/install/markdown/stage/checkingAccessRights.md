The installation wizard **analyzed** your system and produced the following result in the table.

**We recommend** setting:
- for the directories (including nested ones) `./templates`, `./uploads`, `./modules` — **permissions 755**;
- for files nested in the `./modules` and `./templates` directories — **permissions 644**.

Separately, we recommend setting:
- **permissions 770** for the `./backups`, `./cron`, and `./logs` directories;
- for files — **permissions 660**.