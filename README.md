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
