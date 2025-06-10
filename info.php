Issue And Dispense
..................
<!-- For add new issue or dispense form transaction type should show where request mandatory is N.
For respond form transaction type should show where request mandatory is Y.  -->

<!-- For selecting source and destination after selecting transaction type if both source and destination are 
locations then check location applicable to for transaction type if it is source then show controlled 
locations in source only and in destinations all locations should show. do same if location applicable to for 
transaction type if it is destination then show controlled locations in destination 
only and in source all locations should show. -->

For inventory Balance we have to store balance for org, site and locations if source or destination
are locations then we have to store balance for both source or destination according to source action
and destination action.
check location_id and location balance in inventory_balance table
test throughky for external transaction and issue and dispense bith modules

<!-- there is an issue in max qty in respond button -->

<!-- For respond if there is demand qty is 5 and on first go user only issue or dispense 3 then on 
second go demandqty must change like 5 - 3 = 2 and on second go user can issue or dispense only 2 qty. -->

<!-- When click on add more if user select same brand and batch that was selected previously then max for transaction should update according to the above entered quantity. -->

<!-- show batch no as a select option not input type and iof batch numbere is > 1 then user shoudl able to select brand -->


<!-- HUMAN RESOURCE / PREFIX SETUP -->
<!-- 1.	A new form will be created for Prefix Setup, see Table H-14 -->
<!-- 2.	This Prefix filed will be available before Name in a) Employee Setup / Add Employee, b) Third Party Registration / Register a Third Party -->
<!-- 3.	Both Employee names and Third-Party Focal Person’s names will be displayed by joining prefix with name everywhere. -->



...........................................................................
changing before going to live server
create 2 columns in inventory_balance table source_balance and destination_balance
............................................................................
when mutiple time ajax run error change the .change function with like this   
 $(siteSelector).off('change.siteLookup').on('change.siteLookup', function(){