#!/usr/bin/perl 
use strict;
use Getopt::Long;
use POSIX;
use File::Basename;
#--------------------------------------------------
#  Setting environment
#-------------------------------------------------- 
$ENV{"USER"}="opuser";
$ENV{"HOME"}="/home/opuser";
#--------------------------------------------------
#  globlal variables
#-------------------------------------------------- 
our $name = basename($0, ".pl");
our $opt_version = "1.0";
our $path = "/usr/local/opmon/libexec";
our $temp_log = "$path/$name.log";
our ($opt_help, $opt_debug ,$opt_warn,$opt_crit);
our ($datafile,$log_file);

our $dir_log = "/bkp_lojas";
our $localdate = strftime('%d/%m/%Y',localtime);

sub main {

	getoption();
	#print "LOGFILE: $dir_log/$log_file\n";
	open (FILE,"$dir_log/$log_file") or die ("Nao foi posivel abrir o arquivo de log: $!");
        while (<FILE>) {
	    #print "SAIDA: $_\n";
	    if ($_ =~ /__(\S+)__/){
		$datafile = $1;
	    }
        }
	#print "DATAFILE: ($datafile)\nDATALOCAL: ($localdate)\n";
	if ($datafile !~ /(\d+)\/(\d+)\/(\d+)/) {
		print "Unknown - Nao foi possivel verificar o status do servico.\n";
		exit(3);
	}elsif ($datafile != $localdate) {
		print "Critical - Data do Backup: $datafile.\n";
		exit(2);
	}elsif ($datafile == $localdate) {
		print "Ok - Data do Backup: $datafile.\n";
		exit(0);
	}else{
		print "Unknown - Nao foi possivel verificar o status do servico.\n";
		exit(3);
	}
	close (FILE);


}
#--------------------------------------------------------------------------------------
sub getoption  {
	Getopt::Long::Configure('bundling');
	GetOptions(
            'd|debug=i'                 => \$opt_debug,
#            'h|help=s'                  => \$opt_help,
#            'w|warning=f'               => \$opt_warn,
            'c|critical=f'              => \$opt_crit,
            'l|logfile=s'               => \$log_file,
#            's|string=s'                => \$string, 

        )or do{
            printUsage();
            exit();
        };
        if (!$log_file){
            printUsage();
            exit();
        }

        if ($opt_debug){
            $opt_debug = 1;
        }else{
            $opt_debug = 0;
        }

}
#--------------------------------------------------------------------------------------
sub logger {

        return (0) if (not defined $opt_debug);

        my $msg = shift (@_);
        my $log = "$path/$name.log";

        my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst)=localtime(time);
        $wday++;
        $yday++;
        $mon++;
        $year+=1900;
        $isdst++;

        if ($opt_debug == 1){
            print "$msg\n";
        }else {
           open(LOG, ">>$log");
           printf LOG ("%02i/%02i/%i - %02i:%02i:%02i => %s\n",$mday,$mon,$year,$hour,$min,$sec,$msg);
           close(LOG);
        }
}
#--------------------------------------------------------------------------------------
sub printUsage {

       print <<EOB

Usage: $name.pl [OPTION]...

        -l, --log_file     Arquivo de Log

        -d, --debug        1 = Habilita o debug
                           2 = Gera o arquivo de log

EOB

}
#--------------------------------------------------------------------------------------
sub print_error {
    my $msg = shift;
    print "Critical - $msg\n";
    exit (2);
}
&main;

