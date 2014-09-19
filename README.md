# Hiring API

This is a client written for a the code test at a certain network services company which you may or may not be aware of.

## Usage

Run /index.php, providing a filename via STDIN. The file will be read and the commands will be executed.

If an application error occurs, or if an Exception is thrown, execution halts and the error is returned with status 000.

## Notes

* index.php instantiates a Client class. There is a client for each version of the API, and we determine which to load by seeing if the user attempted to auth.
* index.php finds the input file and splits it into separate lines.
* Each line of input is separated into a command and its parameters.
* Each line of input is sent to client in this manner: Client::$command($param1, $param2)
* The client knows how to reformat the command and parameters for the API. It also knows how to reformat data from the API.
* After the client sends the request and processes the data, it builds the string response.
