Issue And Dispense
..................
For add new issue or dispense form transaction type should show where request mandatory is N.
For respond form transaction type should show where request mandatory is Y. 

For selecting source and destination after selecting transaction type if both source and destination are 
locations then check location applicable to for transaction type if it is source then show controlled 
locations in source only and in destinations all locations should show. do same if location applicable to for 
transaction type if it is destination then show controlled locations in destination 
only and in source all locations should show.

For inventory Balance we have to store balance for org, site and locations if source or destination
are locations then we have to store balance for both source or destination according to source action
and destination action.

For respond if there is demand qty is 5 and on first go user only issue or dispense 3 then on 
second go demandqty must change like 5 - 3 = 2 and on second go user can issue or dispense only 2 qty.

Create Module of prefix under human resource like mr, ms etc and this module will add field in add employee
and third party registration form.

...........................................................................
when mutiple time ajax run error change the .change function with like this   
 $(siteSelector).off('change.siteLookup').on('change.siteLookup', function(){