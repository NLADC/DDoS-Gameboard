# DDoS gameboard

Installation: INSTALL.md
Description technical operation: TECHNICAL.md
API documentation: API-DOC.md

This Manual wil only describe native DDOS Gameboard functionality and assume you are familiar with the native WinterCMS features.
``https://wintercms.com/docs/v1.2/docs``

## Summary

The gameboard provides continuous display of the progress of an exercise.

The database includes the participants (parties) with each action.
For each action is recognized:

- start; date/time of action
- length; of action in seconds;
- tags; text; at the bottom of the action block
- name; text; at the top of the action block
- description; text; below name in the action block
- delay; in seconds
- extension; in seconds
- issues; boolean
- cancelled; boolean

## Setup

The gameboard has a Frontend and a Backend part.
Everything frontend has url like this ```<siteroot>/?url```
Everything backend ```<siteroot>/backend/?url```

There are Frontend Users known in the system as Spelbord Users and Backend Users.

# Frontend
## Summary
### Roles
The frontend is the actual gameboard, Frontend users can login using ``/login`` and access the gameboard
Here are 3 roles:
#### Red:
These are users that need to attack dictated items on the board visible within their respective party.
#### Blue:
These are users that need to defend their items on the board from the red team.
#### Purple:
These users are like the referee per party, they need to oversee that everything goes according to plan.

# Backend

## Backend Users
in the ```<siteroot>/backend/``` we have 4 system roles that have access to the backend:
#### Super User
This is a system default Admin with 100% access to everything in the backend interface
#### DDOS Gameboard Administrator
This is a created role that has access to every DDOS gameboard related item in the CMS
#### DDOS Gameboard Manager
This is a tenant role, that is limited by its party. The manager can only edit or view thing within it's own party
#### DDOS Gameboard API User
This is a user that can do nothing in the system except grant access to the rest API and give it an access token

##### I will shorten these as SU, D.Admin D.Manager and D.Api from now on.

### Setting up backend users
A SU can create a D.Admin using the wintercms default way ``/backend/backend/users``

A Backend User with on of the above roles can be created by D.Admin or SU in the ``/backend/bld/ddosspelbord/backenduserpivots``

Then that D.Admin can create D.Manager and assign them to a party

##### Don't create backend users using SU Natively, you can't assign party that way

### DDOS Gameboard Administrator
This is a role that should be used instead if the SU when managing the Gameboard from a top level.
This role has an oversight about everything gameboard related.
It can also import .csv on multiple places and export.

#### Startpage
Shows a data summary of all the data currently in the Gameboard.
You can also start a COUNTDOWN or send a global message to all frontend Spelbord Users in the game.

#### SpelbordUsers
All frontend Spelbord Users are visible here, you can filter per party.
When you change a role of a spelbord user all their data like their logging or attacks will be destroyed.

#### Users
Here we see all the Backend users including yourself. As discussed earlier you create the backend users for the multi tenant system here.

#### Actions
The Actions here will be the actions per Party on the frontend Gameboard.
- start; date/time of action
- length; of action in seconds;
- tags; text; at the bottom of the action block
- name; text; at the top of the action block
- description; text; below name in the action block
- delay; in seconds
- extension; in seconds
- issues; boolean
- cancelled; boolean

For faster editing, or creating more actions without having to click in and out every Action you can click the ``Edit as Plan``  Button.
This will allow you to export the actions to a Plan. Editing the

Action plan will not edit the Actions directly!

#### Action Plans
Here you can view all the Action Plans. They are a way of saving and storing Actions that you don't want LIVE in the Gameboard.
You can save Actions in a Plan for a later DDOS Gameboard exercise.

`Apply Actions` Buttton will add all the Actions in the actionplan into the the Live Actions of the gameboard.

inside the Apply Actions model you can `Clear Current`. This will permanently delete the actions currently loaded in the gameboard. This way you will replace all the actions.

#### Logs
Here all the Logs are gathered that are created in the frontend Gameboard by SpelbordUsers.

#### Attacks
Here all the attacks are gathered that are created in the frontend Gameboard by SpelbordUsers.

#### Monitor
Here is the configuration for everything related to Monitoring the targets that are the subject of attacking or defending from the SpelbordUsers.
See API-DOC.md for more information

### Settings
This will lead to the global settings page here you can
 - Set the Date and Time for the exercise
 - Activate the rest API and Mesurements
 - Many other small settings // Todo: work these out
 - Delete all data or specific data

### DDOS Gameboard Manager
This role can manage all the configuration and data that is bound within a party.
A Manager can export to .csv but may not always be allowed to import.

#### Parties
Here we manage all the participating parties. The parties are the tenants in the multi tenant system and form the key of seperating everything.
Inside a Party we can manage Actions and spelbord Users. These can also be managed in their own: "SpelbordUsers and, Actions" menus.

#### SpelbordUsers
Here are the fronted SpelbordUsers visible that will participate in the game. When you create a user it will automaticly be assigned to your party.

#### Actions
Every Party has Actions but only the actions are visible for your own Party, here individual actions can be saved.
`Edit as Plan` Button export the actions for your current Party to an ActionPlan for faster editing.

#### ActionPlan
Here you can edit ActionPlans, but you can only see Actions that are within your party.

A D.Admin sees the full plan with all Parties and can incorporate. Your changes will go live when the D.Admin applies the actionplan.

#### Logs
Here all the Logs are gathered that are created in the frontend Gameboard by SpelbordUsers within you own party.

#### Attacks
Here all the attacks are gathered that are created in the frontend Gameboard by SpelbordUsers within you own party.

### DDOS Gameboard API User
Can do absolutely nothing except grant access to the rest API described in API-DOC.md

# Fronted
//Todo: write Frontend functionality Docs
