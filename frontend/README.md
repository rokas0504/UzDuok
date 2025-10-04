## Project setup
#### Setup project name. Run it once after forking repository
```bash
make set_project_name project_name="enter_your_project_name"
```
note: instead of "enter_your_project_name" provide project name

#### Setup local environment
Will copy needed files and execute commands in order to launch docker container
```bash
make setup_dev
```

#### Setup static hostname resolution
In a file add this line:
```
127.0.0.1     local.test
```

For windows (open it as admin):
```
C:\Windows\System32\drivers\etc\hosts
```

For linux/macOS (open with sudo):
```
etc/hosts
```

note: project will be available on http://local.test:3000

### Commands

#### Run typecheck
```sh
npx nuxi typecheck
```

#### Run eslint to check code
```sh
npm run lint
```

#### Run eslint to fix code
```sh
npm run lint:fix
```