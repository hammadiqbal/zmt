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
test throughly for external transaction and issue and dispense bith modules

<!-- there is an issue in max qty in respond button -->

<!-- For respond if there is demand qty is 5 and on first go user only issue or dispense 3 then on 
second go demandqty must change like 5 - 3 = 2 and on second go user can issue or dispense only 2 qty. -->

<!-- When click on add more if user select same brand and batch that was selected previously then max for transaction should update according to the above entered quantity. -->

<!-- show batch no as a select option not input type and iof batch numbere is > 1 then user shoudl able to select brand -->


<!-- HUMAN RESOURCE / PREFIX SETUP -->
<!-- 1.	A new form will be created for Prefix Setup, see Table H-14 -->
<!-- 2.	This Prefix filed will be available before Name in a) Employee Setup / Add Employee, b) Third Party Registration / Register a Third Party -->
<!-- 3.	Both Employee names and Third-Party Focal Person’s names will be displayed by joining prefix with name everywhere. -->
<!-- 
HUMAN RESOURCE / EMPLOYEE SETUP
1.	The email should be editable, but as soon as it is modified, a pop-up should give message “The email address of this employee has been modified, 
a new password has been emailed to the employee for login”. -->
<!-- 2.	At the backend, a) email will be updated in User setup, b) current session will be logged out, c) email will be sent to employee with new password. -->

<!-- HUMAN RESOURCE / EMPLOYEE COST CENTER ALLOCATION
1.	Headcount Cost Center should be displayed on Cost Allocation when Employee is selected to avoid any mistake in selection
 -->

 <!-- 1.	Apply filters in following screens in a sequential manner -->
<!-- a.	Item Setup/Item Sub-Category Setup: 		Category
b.	Item Setup/Item Type Setup: 			Category & Sub-Category
c.	Item Setup/Item Generic Setup: 		Category, Sub-Category & Type
d.	Item Setup/Item Brand Setup: 			Category, Sub-Category, Type & Generic -->
<!-- e.	Services/Service Groups:			Service Type -->
<!-- f.	Services/Service Code Directory:		Service Type & Group -->
<!-- g.	Activations/Cost Centers Activation:		Site & CC Type -->
<!-- h.	Activations/Activated Services:		Site,Service Type & Group, CC, and servicemodes -->
<!-- h.	Activations/Service Location Activation:		Site -->
<!-- i.	Human Resource/Position Setup:		Cadre -->
<!-- j.	Territories/Divisions: 				Province -->
<!-- k.	Territories/Districts: 				Province & Division -->
<!-- l.	Cost Centers/Cost Center Setup:		CC Type -->
<!-- m.	Key Performance Indicators/KPI Types:		KPI Group & Dimension -->
<!-- n.	Key Performance Indicators/KPI Setup:		KPI Group, Dimension & Type -->
<!-- o.	Material Management/Stock Monitoring:	Generic & Brand -->

FRONT DESK SERVICES / OUTSOURCED SERVICES
1.	A new form will be created in Front Desk module to record Outsourced Services on receiving of their bill, see Table F-9
2.	Service ID will be generated (just like Patient Arrival & Departure Form)
3.	Along with other details, will have to enter Referral Site Code
4.	Service Mode will be Outsourced by Default (how to avoid Hard Code)
5.	Service Type, Group and Code can be entered (Optional), if available in Service Code Directory
6.	If the above fields are not filled, then an Open Text field will appear to record Service Description
7.	Remarks will be Optional
8.	Service Start Time, End Time and Billed Amount will have to entered
<!-- 
ORGANIZATION / REFERRAL SITE SETUP
9.	A new form will be created in Organization module, similar to Site setup but with lesser fields, see Table M-20
10.	Remarks and Contact numbers will be optional -->




...........................................................................
changing before going to live server
create 2 columns in inventory_balance table add location_id and and location_balance
create sessions table
create table name referral_site
update module table
............................................................................
when mutiple time ajax run error change the .change function with like this   
 $(siteSelector).off('change.siteLookup').on('change.siteLookup', function(){