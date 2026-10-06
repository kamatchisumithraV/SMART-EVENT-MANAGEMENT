#!/usr/bin/perl

use strict;
use warnings;
use DBI;


# ============================================================================
# Database Configuration
# ============================================================================

my $database = "eventdb";
my $host     = "localhost";
my $port     = "3306";
my $username = "root";
my $password = "";


# ============================================================================
# Create Database Connection
# ============================================================================

my $dsn = "DBI:mysql:database=$database;host=$host;port=$port";

my $dbh = DBI->connect(
    $dsn,
    $username,
    $password,
    {
        RaiseError => 1,
        AutoCommit => 1
    }
);


# ============================================================================
# Check Database Connection
# ============================================================================

if (!$dbh) {
    die "Database connection failed: $DBI::errstr\n";
}


# ============================================================================
# Attendance Report Query
# ============================================================================
#
# This query gets:
# - Event name
# - Event date
# - Venue
# - User name
# - User email
# - Registration date
# - Registration status
#
# NOTE:
# The registrations table is expected to contain:
# id
# user_id
# event_id
# registration_date
# status
#
# ============================================================================

my $sql = "
    SELECT
        e.event_name,
        e.event_date,
        e.venue,
        u.name AS user_name,
        u.email,
        r.registration_date,
        r.status
    FROM registrations r
    INNER JOIN events e
        ON r.event_id = e.id
    INNER JOIN users u
        ON r.user_id = u.id
    ORDER BY
        e.event_date ASC,
        e.event_name ASC,
        u.name ASC
";


# ============================================================================
# Prepare SQL Statement
# ============================================================================

my $sth = $dbh->prepare($sql);


# ============================================================================
# Execute SQL Statement
# ============================================================================

$sth->execute();


# ============================================================================
# Display Report Header
# ============================================================================

print "\n";
print "============================================================\n";
print "                 SMART EVENT SYSTEM\n";
print "                 ATTENDANCE REPORT\n";
print "============================================================\n";
print "\n";

printf(
    "%-25s %-12s %-20s %-20s %-30s %-15s %-15s\n",
    "Event Name",
    "Date",
    "Venue",
    "Participant",
    "Email",
    "Reg. Date",
    "Status"
);

print "-" x 137;
print "\n";


# ============================================================================
# Fetch and Display Report Data
# ============================================================================

while (my $row = $sth->fetchrow_hashref()) {

    my $event_name = $row->{event_name} // "";
    my $event_date = $row->{event_date} // "";
    my $venue      = $row->{venue} // "";
    my $user_name  = $row->{user_name} // "";
    my $email      = $row->{email} // "";
    my $reg_date   = $row->{registration_date} // "";
    my $status     = $row->{status} // "";


    # ------------------------------------------------------------------------
    # Print Each Record
    # ------------------------------------------------------------------------

    printf(
        "%-25s %-12s %-20s %-20s %-30s %-15s %-15s\n",
        substr($event_name, 0, 25),
        $event_date,
        substr($venue, 0, 20),
        substr($user_name, 0, 20),
        substr($email, 0, 30),
        substr($reg_date, 0, 15),
        substr($status, 0, 15)
    );
}


# ============================================================================
# Display Report Footer
# ============================================================================

print "-" x 137;
print "\n";
print "Attendance report generated successfully.\n";
print "\n";


# ============================================================================
# Close Statement
# ============================================================================

$sth->finish();


# ============================================================================
# Close Database Connection
# ============================================================================

$dbh->disconnect();


# ============================================================================
# End of Program
# ============================================================================

exit 0;