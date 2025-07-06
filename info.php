Issue And Dispense
..................
There is inventory location in material consumption when data in issue Dispense module, it should check 
if destination is location then destination prefilled with material_requisition inventory location.

<!-- check this fnctionn SiteChangeActivatedServiceLocation
for inventory location we need to fetch only those location wher inventory status is enable -->

<!-- 1.	A new form will be created that can be accessed through main module of Material Management with name of “Requisition for Other Transactions”.
2.	This form will be used to Request for Internal Transfer or Condemnation (or any other transaction labelled as Other Transaction)
3.	Practically this will be copy of “Requisition for Material Consumption” wit the difference that no patient or service details are required, see Table I-19.
4.	The form will display all the requisitions made so far for in the following grid. There will be no top panel, like previous form.

5.	Clicking “Add Other Transactions Requisition” at the right top corner should take to another form where following fields will have to be entered in the top panel of new form: 
a.	“Requested Transaction”, the dropdown will show only those transaction types which are tagged with other transactions and where request is mandatory, see Table I-9
b.	If the selected transaction also requires mandatory requesting location (Table I-9), then another dropdown should display with allotted inventory locations for that employee as per site
c.	Remarks, NOT mandatory
6.	Transaction datetime will be saved as Effective Datetime.
7.	In the same top panel, following fields will have to be entered:
a.	Org. Code		Display (for developers only), Select one, Save
b.	Site Code		Display, Select One, Save
8.	Below the top panel, following fields will be entered in a tabular way. This means one requisition may have multiple entries like below. All generics (medical and/or general) will appear in the dropdown. 
 -->
<!-- in issue dispense if i add new issue with two locations where org and site is so in that case org and site balance data should remain
same noa ddition subtraction required for that thing -->

<!-- Finalize Other Transaction Module 
Show balances and check repond btn update validation logic if source or destination is hide then these fields should not required -->
Finalize Consumption
Show data int table get data from inventory_management table where transaction activity is issue and dispense and
transaction name like '%issue%'
then work on consume button
...........................................................................
add columns (dose,route_id,frequency_id,duration) in inventory_management table
remove source_balance,destination_balance,site_balance,org_balance from inventory_management table
............................................................................
when mutiple time ajax run error change the .change function with like this   
 $(siteSelector).off('change.siteLookup').on('change.siteLookup', function(){
