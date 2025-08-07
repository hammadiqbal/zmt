...........................................................................
HUMAN RESOURCE / EMPLOYEE SERVICE ALLOCATION
<!-- a.	For Encounters & Procedures, the Performing Cost Center should be in Employees’s Cost Center Allocation
b.	For Investigations, the Billing Cost Center should be in Employee’s Cost Center Allocation. -->


<!-- FRONT DESK SERVICES / PATIENT ARRIVAL & SERVICE BOOKING
1.	Patient Cell no. should also be displayed with MR no. -->


FRONT DESK / PATIENT ARRIVAL & DEPARTURE
without any duplication of MR no, Arrival Date, Service Mode, Service Code, Billing CC and Responsible Person.


MEDICAL RECORD / ENCOUNTERS & PROCEDURES
5.	Cross-check following
    a.	Only User based Cost Centers (Employee Cost Center Allocation) should be displayed in dropdown of Performing Cost Center
    b.	These should be further filtered for the specific service being displayed (Employee Service Allocation)
6.	If the Performing Cost center is NOT listed as Billing Cost Center, then disable Encounter, Procedure, Investigation, Medicine requisition buttons.
<!-- 7.  Show Remarks in the screen if it is available if not available then show N/A --> 

PATIENT MEDICAL RECORD / ENCOUNTERS & PROCEDURES / REQUISITIONS
d.	The services data will be filtered as per; i) Services activated for that site, and ii) Services allocated to that employee.
5.	List of Investigations and Procedures to be ordered/planned should be displayed with multi-select option

PATIENT MEDICAL RECORD / INVESTIGATIONS TRACKING
5.	This form should also have separate access from main menu.

<!-- MATERIAL MANAGEMENT / EXTERNAL TRANSACTIONS -->
<!-- All vendors are not displayed, please check  -->

MATERIAL MANAGEMENT / REQUISITION FOR OTHER TRANSACTIONS
All locations should be as per Site Location Activation


Check Service Scheduling in all modules

.........................................................................
check before upload to hosting
.........................................................................

............................................................................
when mutiple time ajax run error change the .change function with like this
 $(siteSelector).off('change.siteLookup').on('change.siteLookup', function(){
