# How it works


### Workflow

Firstly, You must connect to social network!

Synchronize with social network - collect last publish posts.
When You save Active Record model then synchronization model is created.
When You delete Active Record model then synchronization model is deleted only if `SynchronizerBehavior` has option `syncDelete` is `true`.


### Notice

If you have something wrong in configuration of `Synchonizer` component you'll get exception when you model will create or delete.
Synchronization is support only post creating or post deleting that means if you update content of your Active Record model, content will not be updated for corresponding post in social networks.