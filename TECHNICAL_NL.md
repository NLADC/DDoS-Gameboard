#Technische werking

DDoS Gameboard (GB) is een applicatie gebaseerd op het VUE (frontend) en WinterCMS
framework.

Centraal staat 1 pagina (home) met het spelbord geladen vanuit gameboard.js.
Deze pagina laadt vanuit de server alle partijen en acties en gaat
vervolgens een stream openzetten naar de backend (/api/) en wachten op transacties.

Rechtsbovenin zit een LOGIN knop. Daarmee log je in op de backend onder een spelbord
account. Afhankelijk van de rol, krijg je rechten op het spelbord.

BLUE en RED teamleden hebben alleen rechten om logging op te nemen. Dit doe je
door aan de linkerkant op de tijdlijn te klikken. Dan opent een popup waarin je
de log kunt invoeren. Bij een submit worden deze logging op de backend opgeslagen
en wordt hier een transactie van gemaakt die vervolgens bij alle clients terugkomt.

BLUE teamleden van 1 partij zien elkaars logging. Zo ook RED teamleden van 1 partij.
De PUPLE teamleden van dezelfde partij zien de logging van BLUE en RED teamleden.

Een beheerder kan via de backend tijdens de oefening acties verschuiven en/of
aanpassen. Via ook weer een transactie worden deze bijgewerkt op de clients.

##Opmerkingen

- De opzet is via een stream omdat deze sneller is en minder belastend is voor de
client browser. Een stream (javascript EventSource) is geoptimaliseerd om
tegenlijk met client browser acties te bestaan.
- Er zijn ook SYSTEM transacties; deze zijn in de wintercms omgeving nog niet
verder uitgewerkt.



