#DDoS spelbord

Installatie: INSTALL.md
Beschrijving technische werking: TECHNICAL.md

##Functionaliteit

Het spelbord voorziet in het doorlopende tonen van de voorgang van een oefening.

In de database zijn de deelnemers (partijen) opgenomen met ieder acties.
Per actie wordt onderkend:

- start; datum/time
- length; seconds;
- tag; string; onderaan action block
- name; string; bovenaan in action block
- description; onder name in action block
- delay; seconds; vetraging
- extension; seconds; verlenging
- issues; boolean;
- cancelled; boolean; geannulleerd



