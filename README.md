# Collaborative Problem Solving XBlock (cpsx)

The purpose of this XBlock is to allow small groups to work on a set of edX problems together through real-time chat.
The problems in the assignment can be any type of edX problem, e.g. numerical input, drag-and-drop, etc. 

## Installation

As described in the file INSTALL.txt, requires configuring the chat app and apache virtual host, creating the necessary
database on the MySQL server, installing the xblock, and manually copying some style files.

## Basic Functionality

* To create a collaborative assignment in Studio, make sure "cpsx" is added to advanced modules list
* Insert an (advanced type) Collaborative Problem Solving element in any unit, and edit the following:
  * room name, which allows a single chat session to follow students across different screens within an assignment
  * group size of a collaboration cohort, e.g. 2 or 3 students
  * wait time, e.g. 5 minutes, to synchronize students before starting the assignment 
and to put a limit on the time a student will wait in the event that no partners are available
* A copy of the CPS unit must be placed on every page where the chat should appear. 
The CPS element will check for an active chat session of the current user and load the chat transcript.
* Students who might lose their network connection will be able to rejoin their last conversation; for this reason, they must explicitly 
log out of their chat session to remove themselves from the active membership list. 
(It is straightforward to create garbage-cleaning scripts to close out active chat sessions.)
* Chat logs and chatroom membership assignments are stored in a MySQL database called ajax_chat.

## See wishlist.md for ways to contribute

