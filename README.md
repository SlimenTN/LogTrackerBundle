# LogTrackerBundle

Sometimes symfony does not display the exception in the browser, so you have to go to the log file and check the lastest log details so you can read the exception and locate the source of the problem.<br>
Pretty annoying isn't ? Well `LogtrackerBundle` will make your life easier by displaying the details of the log file (dev.log and prod.log) in more elegant way with the possibility of filtering and searching inside the file.<br>
`LogTrackerBunlde` helps you also to track any thrown exception in your project by sending real time email with the details of the exception.

# Installation

1. `composer require slimen/log-tracker`<br>
2. Enable the bundle in AppKernel.php `new SBC\LogTrackerBundle\LogTrackerBundle(),`<br>
3. Add this in `config/routing.yml`:<br>
    ```
    log_trucker:
        resource: "@LogTrackerBundle/Resources/config/routing.yml"
        prefix:   /logger
    ```

# Usage
1. To display `dev.log` got to `localhost:/YourProject/web/dev_app.php/logger/_dev`
2. To display `prod.log` got to `localhost:/YourProject/web/dev_app.php/logger/_prod`
3. To keep track of thrown exception you need to add this in your `config.yml` file:<br>
    ```
    # LogTracker Configuration
    log_tracker:
        app_name: 'Your app name'
        sender_mail: 'your_mail@company.com'
        handler_text: 'Text will be displayed when LogTrackerBundle handle the error'
        recipients: ['mail1@company.com', 'mail2@company.com'] #you can add as much as you want of addresses
    ```
