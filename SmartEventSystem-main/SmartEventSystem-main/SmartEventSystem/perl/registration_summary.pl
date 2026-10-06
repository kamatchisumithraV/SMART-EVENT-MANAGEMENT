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
# Registration Summary Query
# ============================================================================
#
# This query gets:
# - Event name
# - Category
# - Event date
# - Venue
# - Maximum participants
# - Number of registrations
#
# ============================================================================

my $sql = "
    SELECT
        e.event_name,
        e.category,
        e.event_date,
        e.venue,
        e.max_participants,
        COUNT(r.id) AS total_registrations
    FROM events e
    LEFT JOIN registrations r
        ON e.id = r.event_id
    GROUP BY
        e.id,
        e.event_name,
        e.category,
        e.event_date,
        e.venue,
        e.max_participants
    ORDER BY
        e.event_date ASC,
        e.event_name ASC
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
print "              REGISTRATION SUMMARY\n";
print "============================================================\n";
print "\n";

printf(
    "%-25s %-18s %-12s %-20s %-12s %-15s\n",
    "Event Name",
    "Category",
    "Date",
    "Venue",
    "Max. Seats",
    "Registrations"
);

print "-" x 102;
print "\n";


# ============================================================================
# Variables for Overall Summary
# ============================================================================

my $total_events        = 0;
my $total_registrations = 0;


# ============================================================================
# Fetch and Display Report Data
# ============================================================================

while (my $row = $sth->fetchrow_hashref()) {

    my $event_name = $row->{event_name} // "";
    my $category   = $row->{category} // "";
    my $event_date = $row->{event_date} // "";
    my $venue      = $row->{venue} // "";
    my $max_seats  = $row->{max_participants} // 0;
    my $registrations = $row->{total_registrations} // 0;


    # ------------------------------------------------------------------------
    # Update Overall Summary
    # ------------------------------------------------------------------------

    $total_events++;

    $total_registrations += $registrations;


    # ------------------------------------------------------------------------
    # Print Event Registration Details
    # ------------------------------------------------------------------------

    printf(
        "%-25s %-18s %-12s %-20s %-12s %-15s\n",
        substr($event_name, 0, 25),
        substr($category, 0, 18),
        $event_date,
        substr($venue, 0, 20),
        $max_seats,
        $registrations
    );
}


# ============================================================================
# Display Report Footer
# ============================================================================

print "-" x 102;
print "\n";


# ============================================================================
# Display Overall Summary
# ============================================================================

print "\n";
print "Overall Summary\n";
print "---------------\n";

print "Total Events        : $total_events\n";
print "Total Registrations : $total_registrations\n";

print "\n";
print "Registration summary generated successfully.\n";
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