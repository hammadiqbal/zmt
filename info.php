

EXTERNAL TRANSACTIONS
Write some logic for showing source and destination balance
............................................................

ISSUE AND DISENSE
in below table Fetch details from requisition for medicines and requisition for material consumption
status should select by user

............................................................................................................................................................
Prompts should be added from stock monitioring see details in excel file

Show Quantities from issue and dispense

Multi Select Service  option in Req first select service modes then  show there services 
in req for investigation.

<!-- Check Status management for ReqEPi and patient arrival and departure
If patient is not arrived then remove all btns in investigaton tracking -->

Add Filter for encounter, procedure and investigation in patient arrival and departure module

Create one other module name investigation confirmation show all (multi select) services there and patient arrived in same time for all services.

<!-- check expiry check for external transaction if expiry check is yes then expired items should select previous or future date
there is 0 restriction for this if no then expired items should select only in future date -->

<!-- check mechanism for updating status from active to inactive for these 3 modules
patient arrival & departure, and Requisition For EPI
if service end and patient arrival status set from  active to inactive then the 
should also set inactive for requisition for epi according to mr, emp_id, servieid, service_mode_id and biling CC
where status must be = 1 -->

check issue and dispense and add more btn functinality in all other form and then finaliza issue & dispense and show data
from requisition for medication and material consumption and insert data into inventory_management table

...........................................................................
ISUUE & DISPENSE
now show auto batch no and expiry date while new issue and dispense 
and in respond btn show auto batch_no and expiry date after select brand 
if same generic and brand has one batch the it should auto select if more than 1 then user should select

...........................................................................
Changes required before deployment
chnage source and destination coulm in inventory_management table update int to var_char
...........................................................................

when mutiple time ajax run error change the .change function with like this   
 $(siteSelector).off('change.siteLookup').on('change.siteLookup', function(){
