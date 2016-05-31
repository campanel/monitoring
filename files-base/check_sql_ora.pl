#!/usr/bin/perl
#Author:
#        cleber.motta@opservices.com.br)
#	31-01-2012

use strict;
use Getopt::Long;
use POSIX;
use File::Basename;
use DBD::Oracle;

# Setting environment
$ENV{"USER"}="opuser";
$ENV{"HOME"}="/home/opuser";


#$ENV{"LD_LIBRARY_PATH"}="/usr/lib/oracle/11.1/client/lib";
#$ENV{"TNS_ADMIN"}="/usr/lib/oracle";
#$ENV{"PATH"}="/usr/kerberos/bin:/usr/local/bin:/bin:/usr/bin:/usr/lib/oracle/11.1/client/bin:/home/opuser/bin";
#$ENV{"LD_RUN_PATH"}="/usr/lib/oracle/11.1/client/lib";
#$ENV{"ORACLE_HOME"}="/usr/lib/oracle/11.1/client";

$ENV{"ORACLE_HOME"}="/usr/local/oracle";
#$PATH="/usr/local/oracle/bin:/usr/local/oracle/JRE/bin:$PATH";
#$PATH="/usr/kerberos/sbin:/usr/kerberos/bin:/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin:/usr/lib/oracle/11.1/client/bin:/root/bin:/usr/lib/oracle/11.1/client/bin";
$ENV{"LD_LIBRARY_PATH"}="/usr/X11R6/lib:/usr/lib:/lib:/usr/local/oracle/lib:/usr/local/oracle/JRE/lib:/usr/local/lib";


our $name = basename($0, ".pl");
our $path = "/usr/local/opmon/libexec";
our ($oHelp, $oVerbose, $oWarn, $oCrit, $oTnsname, $oPort, $oUser, $oPass, $oDate, $oQuery, @result, $result, $msg);
our $opt_debug = 0; #1 enabe

#--------------------------------------------------------------------------------------
sub main {
        getOption();
        logger("Starting...");
        invokeQuery();
	$msg = $result;
	$msg .= "\n";
	if ($result){
		print "OK - $msg";
                exit(0);
	}
	if(!$result){
		print "CRITICAL - $msg";
	        exit(2);
	}
	else{
		print "WARNING - $msg";
		exit(1);
	}
}

#--------------------------------------------------------------------------------------
sub invokeQuery {
        logger("Inicio query");
        #my $query = "SELECT * FROM DUAL";
        my $query = $oQuery;
        logger("Execute query: $query");
        my ($dbh, $sth);
        printUsage() if ((!$oUser) or  (!$oPass));
        $dbh = DBI->connect("dbi:Oracle:$oTnsname", "$oUser", "$oPass");
        $sth = $dbh->prepare($query);
        $sth->execute or logger("ERROR: Can't execute the SQL statement: $DBI::errstr");
	$result = $sth->fetchrow_array;
        $sth->finish;
        $dbh->disconnect;
}
#--------------------------------------------------------------------------------------
sub quit {
        my $mgs = shift;
        my $code = shift;
        print $mgs,"\n";
        exit($code);
}
#--------------------------------------------------------------------------------------
sub getOption  {
        Getopt::Long::Configure('bundling');
        GetOptions(
                'h|help' => \$oHelp,
                'v|verbose' => \$oVerbose,
                'T|tnsname=s' => \$oTnsname,
                'p|port=i' => \$oPort,
                'U|username=s' => \$oUser,
                'P|password=s' => \$oPass,
                'q|query=s' => \$oQuery,
        );
        if($oHelp){
                printUsage();
                exit(1);
        }
        if ((!$oUser) or (!$oPass)){
                printUsage();
                exit(1);
        }
}
#--------------------------------------------------------------------------------------
sub logger {
        if($opt_debug > 0){
		my $msg = shift (@_);
		my $log = "$path/$name.log";
		my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst)=localtime(time);
		$wday++;
		$yday++;
		$mon++;
		$year+=1900;
		$isdst++;
		open(LOG, ">>$log");
		printf LOG ("%02i/%02i/%i - %02i:%02i:%02i => %s\n",$mday,$mon,$year,$hour,$min,$sec,$msg);
		close(LOG);
	}
}
#--------------------------------------------------------------------------------------
sub printUsage {
       print <<EOB
Usage: $name.pl [OPTION]...
-h, --help
-v, --verbose
-T, --tnsname
-p, --port
-U, --username
-P, --password
-q, --query
uso: ./check_oracle_query.pl -T tnsname -U user -P senha -q "query"

EOB
}
#--------------------------------------------------------------------------------------

&main;

