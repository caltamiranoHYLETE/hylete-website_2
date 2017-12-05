# README, Seriously #

Welcome to the HYLETE website code repository.

This repo has been set up for us at HYLETE to develop, build and deploy our site. While we are very technically proficient here at HYLETE, we are not a development agency steeped in rigid structure, release schedules, ticketing systems etc. We are not experts in dev-ops and we don't want to be. We try and be as safe as possible but understand we need need to move at a pace that allows us to be nible.

We try to keep it simple and would appreciate it if you did too.

### How do I get set up? ###

You'll need to set up your dev environment using the code from the repo and the database, which is in a separate repo. The media folder is missing from the repo as it is huge and changes daily. If you really need images, let us know and we'll come up with something.

The database is configured to use dev.hylete.com. You'll need to update your hosts file or change the database config_core_data tables to reflect this.

You may need to change the local.xml file to connect to the dev database. We have had some issues where the dev local.xml has stomped on staging so make sure it is ignored when you commit. Contact us and we'll give you the updated database info if you get an error loading the site.

You can commit using the development branch with pull requests on initial commits. Once we are up and running, you can work in the default branch for fine tuning. You are reading this because we trust you. If YOU trust YOU, we're all good.

### How do I get this deployed? ###

We have 3 environments to move code to.

http://hylete-dev.vaimo.com
This is the first place we will test and install new code, modules, change configuration etc. This is a good place to move code that you want to see how it will react and play in our environment.


https://staging.hylete.com (Basic Auth - contact us for login when needed)
This should be considered for more polished, production ready versions of your code. If you are unsure about your changes, throw them up on the dev server first.


http://www/hylete.com (production)
The big time, the show, the dance. If you are asking us to move code here, you better feel confident that nothing is going to go wrong.

Deployments will be done by us unless some special arrangement is made. Currently we move code using DeployHQ.

### What else? ###

If you are using Composer, I prefer to run the composer files locally and commit those changes as opposed to running composer in the environments.

I'm sure I forgot something that you want to know, so just email me and I'll try and get any of your questions answered.

### Who do I talk to? ###

Scott Kennerly, skennerly@hylete.com

