


...........................................................................
Requisition For Other Transaction
<!-- Show Inventory Location based on transaction type allocated locations. -->
<!-- Change Requisition For Other Transaction according to excel select 2 site with their location -->
<!-- Show source site, location and destination site and location based in transaction type selection if both are 
location then show both else show speciic based on transaction type -->


Other Transaction:
Finalize Add Other Transaction , now we have 2 sites in other transactions

check which site to be used for fetching brand details add condition here according to the source or destination 
action based on subtraction or reversal

Reversal Transaction:
Show all transactions

check before upload to hosting
.........................................................................
update table requisition_other_transaction
add colum d_site_id in inventory_management table



............................................................................
when mutiple time ajax run error change the .change function with like this   
 $(siteSelector).off('change.siteLookup').on('change.siteLookup', function(){
