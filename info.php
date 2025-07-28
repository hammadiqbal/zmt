


...........................................................................
Requisition For Other Transaction.
<!-- Show Inventory Location based on transaction type allocated locations. -->
<!-- Change Requisition For Other Transaction according to excel select 2 site with their location -->
<!-- Show source site, location and destination site and location based in transaction type selection if both are 
location then show both else show speciic based on transaction type -->

<!-- 
Other Transaction:
check addothertransaction module one more time properly
show all balnace is source and destination both are available the show both details in balance if any one details 
are available then show details according to the availability. -->

<!-- check which site to be used for fetching brand details add condition here according to the source or destination 
action based on subtraction or reversal -->

Reversal Transaction:
Complete Reversal Transaction and Update Logs module in whole software.

.........................................................................
check before upload to hosting
.........................................................................

............................................................................
when mutiple time ajax run error change the .change function with like this   
 $(siteSelector).off('change.siteLookup').on('change.siteLookup', function(){