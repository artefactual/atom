#!/bin/bash

# information objects should be imported first
./symfony csv:import lib/task/import/example/rad/example_information_objects.csv

# example event import
./symfony csv:event-import lib/task/import/example/rad/example_events.csv

# Qubit's relation data needs to be rebuilt after importing
./symfony propel:build-nested-set
