#Technical operation

DDoS Gameboard (GB) is an application based on the VUE (frontend) and WinterCMS
frame.

The homepage is where all the action is, the game board is loaded with gameboard.js.
This page loads from the server all games and actions and goes, it  then opens
a stream to the backend (/api/) and waits for transactions.

At the top right is a LOGIN button. With that you log in to the backend under a game board
account. Depending on the role, you get rights to the game board.

BLUE and RED team members only have permission to record logging. You do this
by clicking on the timeline on the left. Then a popup opens in which you
can enter the log. With a submit, these logging are stored on the backend
and this is made into a transaction that is then returned to all clients.

BLUE team members of 1 party see each other's logging. This also applies to RED team members of 1 party.
The PUPLE team members of the same party see the logging of BLUE and RED team members.

An administrator can shift actions via the backend during the exercise and/or
To adjust. These are updated on the clients via another transaction.

##Comments

- The setup is via a stream because it is faster and less stressful for the
client browser. A stream (javascript EventSource) is optimized to
against client browser actions.
- There are also SYSTEM transactions; these are not yet available in the wintercms environment
further elaborated.
