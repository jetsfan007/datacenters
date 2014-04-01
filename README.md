datacenters
===========

datacenters

scrapes all commercially available datacenters of the world from www.datacentermap.com
Finds their Country, Area, City, Datacentername, Postalcode, Street Address, and tries to reverse lookup their geolocation in openstreetmap or google maps API.

** shortcommings **

- Very little exception handling (it does run through with the current website status)
- Does not check for duplicates (no identifyer) - so if you run scraper again, it will add the whole dataset again into the table
- Best effort location lookup (but with accuracy statement of found locations)
- does not try to find datacenter capacity or pricing
