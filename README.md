# Collaborative Problem Solving Xblock (cpsx)

The purpose of this XBlock is to allow small groups to work on a set of edX problems together through real-time chat.
The problems in the assignment can be any type of edX problem, e.g. numerical input, drag-and-drop, etc. 

## Installation

Installation, described in the file install.sh requires configuring the chat app and apache virtual host, installing the xblock, 
and manually copying some style files.

## Basic Functionality

* The instructor creates in Studio an (advanced type) Collaborative Problem Solving Unit assignment and decides the following:
  * the room name, which allows a single chat session to follow students across different screens within an assignment
  * the size of a collaboration cohort (group size), e.g. 2 or 3 students
  * the wait time, e.g. 5 minutes, to synchronize students before starting the assignment 
and to put a limit on the time a student will wait in the event that no partners are available
* A copy of the CPS unit must be placed on every page where the chat should appear.
* Students who may lose their connection will be able to rejoin their last conversation; for this reason, students must explicitly 
log out of their chat session to remove themselves from the active membership list.




