Clifton
=======

A bridge between ProgrammesDB and /programmes on Forge. Clifton provides a HTTP
API that is consistant with APS's output, so that it can be a drop-in
replacement data source for /programmes on Forge.

Development
-----------

Install development dependencies with `composer install`.

Run tests and code sniffer with `script/test`.

We recommend Clifton is developed locally using the 
[programmes-cloud-sandbox](https://github.com/bbc/programmes-cloud-sandbox),
which contains instructions on how to run Clifton, and Faucet (which provides
the DB schema and tools to ingest data into the DB) within a single VM. This
shall provide the tools to create and populate the Database that Clifton
requests data from.

</readme>

Profiling
-----------
There is a long-lived (hopefully) profiling branch named profiling-build which brings
in tideways (basically xhprof updated for PHP7), a GUI and a few other things
which you can run on Cosmos INT. It uses https://github.com/bbc/programmes-xhprof to setup the profiling.
 Got to that repository to see the available configuration options.

How to do this:
Checkout the profiling-build branch, rebase it on master and deploy this branch to INT. This assumes that the 
code you want to profile is on master of course. 

Visit https://clifton.int.api.bbci.co.uk/whatever/your/route/is?__profile=1 (Note the double underscore).
Load that at least 5 times to make sure that everything that should be cached is cached.
Now visit https://clifton.int.api.bbci.co.uk/xhprof/xhprof_html/index.php .
You should see a list of your visits along with a load of metrics on execution. 


License
-------

This repository is available under the terms of the Apache 2.0 license.
View the LICENSE file for more information.

Copyright (c) 2017 BBC
