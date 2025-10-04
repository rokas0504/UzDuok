# Project setup

#### Setup project name. Run it once after forking repository
```bash
make set_project_name project_name="enter_your_project_name"
```
note: instead of "enter_your_project_name" provide project name

#### Setup local environment
Will copy needed files and execute commands in order to launch docker containers
```bash
make setup_dev project_name="enter_your_project_name"
```
note: instead of "enter_your_project_name" provide project name

## Commands

Artisan commands can be executed from local machine by using sail path
ex: to execute create model named test `php artisan make:model test` use sail path:
```bash
./vendor/bin/sail artisan make:model test
```

Show all available make commands:
```bash
make
```

## Alias

To set alias for `./vendor/bin/sail`  
In home directory edit/create `~/.zshrc` or `~/.bashrc` file and add this line:
```
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
```
note: alias will be `sail` you can change alias to be anything else just change `sail` variable before equal to your preferred alias  
note2: for alias to work make sure to restart shell

Example of artisan command using alias:
```bash
sail artisan migrate
```

## Postman

### pre-request script
In `Scripts` tab `Pre-request` section add script for XSRF-TOKEN:
```javascript
pm.sendRequest({
    url: 'local.test/sanctum/csrf-cookie',
    method: 'GET'
}, function(error, response, {cookies}) {
    pm.collectionVariables.set('csrf-token', cookies.get('XSRF-TOKEN'))

    pm.request.addHeader({
        key: "Origin",
        value: "http://local.test:3000"
    });

    pm.request.addHeader({
        key: "Referer",
        value: "http://local.test:3000"
    });
})
```

Make sure to add header in postman `Headers` tab:  
key: X-XSRF-TOKEN  
value: {{csrf-token}}
