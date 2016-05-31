#!/usr/bin/perl 
#Author:
#        cleber.motta@opservices.com.br)
use strict;
use Getopt::Long;
use POSIX;
use File::Basename;
# Setting environment
$ENV{"USER"}="opuser";
$ENV{"HOME"}="/home/opuser";
# Global variables
our $name = basename($0, ".pl");
our $path = "/usr/local/opmon/libexec";
our ($oHelp, $oVerbose, $oWarn, $oCrit, $perf);
#--------------------------------------------------------------------------------------
sub main {
	getoption();
		


}
#--------------------------------------------------------------------------------------
sub metric {
	my $value = shift;
	if ($value >= $oCrit) { return 2
	}elsif ($value >= $oWarn) { return 1
	}elsif ($value < $oWarn) { return 0
	}else{ quit("Unable to check.",3) }
}
#--------------------------------------------------------------------------------------
sub quit {
	my $mgs = shift;
	my $code = shift;
	print $mgs,"\n";
	exit($code);
}
#--------------------------------------------------------------------------------------
sub getoption  {
	Getopt::Long::Configure('bundling');
	GetOptions(
		'c|critical=i' => \$oCrit,
		'h|help' => \$oHelp,
		'v|verbose=i' => \$oVerbose,
		'w|warning=i' => \$oWarn,
		'D|directory=s' => \$oDir,
        );
	if($oHelp){
		printUsage();
		exit(1);
	}
	if ((!$oWarn) or (!$oCrit) or (!$oDir)){
		printUsage();
		exit(1);
	}
}
#--------------------------------------------------------------------------------------
sub logger {
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
#--------------------------------------------------------------------------------------
sub printUsage {
       print <<EOB
Usage: $name.pl [OPTION]...

-c, --critical
-h, --help
-w, --warning
-v, --verbose
-D, --directory

EOB
}
#--------------------------------------------------------------------------------------
&main;
