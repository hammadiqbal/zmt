<!-- Employee CC Allocation:Site & Cost Center entered through Employee Setup will be displayed on top as “Headcount Site” & “Headcount Cost Center”
-->
HUMAN RESOURCE / EMPLOYEE DOCUMENTS: Add Module for employee documnent where user can select any employee after user can able to upload, view or 
delete employee documents.

<!-- EMPLOYEE SERVICE ALLOCATION: Add Select All Options

SERVICES REQUISITION SETUP: After Activating Service for an organization (irrespective of the site) data should 
auto enter in service requisition setup table with mandatory yes, after that remove add Service Requisition btn and modal.shift this moduls in Activation Tab
Remove Add Service Requisition Option in service Requisition Module, remove add option in rights in order to do thi update rights table  -->

<!-- SERVICES BOOKING: If patient arrived then remove Schedule Service Button just show booked or unbooked, if patient not arrived yet then schedule service button should display.
Add condition on arrival data (only date no time) if arrival date is not added then it should add condition on booking date.
Patient Status and Priority, for booked patients, should be auto-fetched while clicking “Confirm Patient Arrival”. -->

<!-- PATIENT ARRIVAL & DEPARTURE: Add condition on arrival data (only date no time) if arrival date is not added then it should add condition on booking date. -->


<!-- PATIENT MEDICAL RECORD / MEDICAL CODING: Upload Diagnosis Medical Coding Data -->

ACTIVATIONS / PROCEDURE CODING: Add module Procedure Coding where all medical coding should which is used for procedure, two fields to entered to map
organization and service.


PATIENT MEDICAL RECORD / VITAL SIGNS": Update MR no add select option.
show all data if patient is arrived the show vital sign with all options otherwise only show vitalsigns in view mode.


PATIENT MEDICAL RECORD / ENCOUNTERS & PROCEDURES: currently encoutner & procedure shwoing data according to service booking, it should be change to patient arrival and departure,
if patient is arrived then show all detail if patient is not arrived then show only in view mode, if patient mr# is not exist in our db then show invalid MR#.
Update MR no add select option.
if there are two setvice activated for same MR # if yes then show select service option then after selecting service show all details.
if there is one service activate for that mr # teh directly show all details without showing option of selecting service.


Before Deployment
Update Table emp_cc 
Update Module Table for service requisition Location Change 
upload icd_code before deployment
