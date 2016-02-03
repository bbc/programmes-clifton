Alfred
======

A Butler to provide programmes information


Development
-----------


Alfred uses PHP 7.0. On your host machine, Assuming you're using homebrew, you
can install it by running:

```sh
brew install php70
```

For the Sandbox, we provide a Vagrant file that shall setup a sandbox containing
everything you need.

Create a copy of your dev certificate in pem format in
`vagrant/dev.bbc.co.uk.pem` and editing `vagrant/env.json` to reflect your proxy settings. 
Then create your sandbox by running:

```sh
vagrant up
```
