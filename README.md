# Rhino Fields

## Introduction

Rhino Fields are EditableFormField that implement the interface RhinoMarkedField.
These fields, once added to a RhinoAssessment, can be marked (pass, fail).
The process of marking is automatic, done upon submission (see RhinoSubmittedFormField extension in rhino-lite).

## Requirements

 * SilverStripe ^5
 * Rhino Lite ^3

## Installation

Installation can be done either by composer or by manually downloading the
release from Github.

### Via composer

`composer require "dnadesign/rhino-fields"`

### Manually

 1.  Download the module from [the releases page](https://github.com/silverstripe/silverstripe-siteconfig/releases).
 2.  Extract the file (if you are on windows try 7-zip for extracting tar.gz files
 3.  Place this directory in your sites root directory. This is the one with framework and cms in it.

 ## Field types

This module comes with the following fields by default:

 * Text: this field expects the value to match the expected text based answer.
 * Mulichoice: radio button field with one or multiple right answers.
 * Timer: readonly field displaying the time ellapsed from the start of the assessment. Time limit optional.

 ## Create your own field

 You can create your own field by extending any EditableFormField and implement RhinoMarkedField.
 Then, implement a validation method pass_or_fail that returns either none, pass or fail (lowercase);

 ## Field specific instructions

 ### RhinoOrderableOptions Field

 This field provide the basic backend functionnality to build a re-orderable list.
 The idea is to have a hidden field that will hold an array of EditableOption ids, which value would be updated by javascript when re-ordeing the list.
 However, the frontend mechanism is not supplied with the field. We recommend you include it in your themes folder to suit your need.

 Note: The frontend mechanism has been developed on[ACC RideForever](https://rideforever.co.nz).
 It relies on [https://github.com/gardiner/draganddrop/](https://github.com/gardiner/draganddrop/).
