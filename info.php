Issue And Dispense
..................
There is inventory location in material consumption when data in issue Dispense module, it should check 
if destination is location then destination prefilled with material_requisition inventory location.

check this fnctionn SiteChangeActivatedServiceLocation
for inventory location we need to fetch only those location wher inventory status is enable


...........................................................................
create table requisition_other_transaction
update module table 
create column requisition_for_other_transaction in rights table
............................................................................
when mutiple time ajax run error change the .change function with like this   
 $(siteSelector).off('change.siteLookup').on('change.siteLookup', function(){
