#!/usr/bin/env python2.6
"""
DESCRIPTION

    Clase generica para convertir la aplicacion en un servicio(demonio) en 
    linux haciendola correr en modo background y estableciendo la entrada y 
    las salidas standard (stdin, stdout, stderr) a los archivos determinados 
    para el sistema.

EXAMPLES
	Esta clase se utiliza desde el archivo astqad.py mediante la clase
	MyDaemon la cual es una subclase que permite la implementacion de 
	los metodos.

EXIT STATUS

	Entrega una instancia de la clase Daemon

AUTHOR

    CL3K LTDA <contacto@cl3k.com>

LICENSE
    This code is licensed under the terms of the 
    Contractual License Program (CLP) to Cinta Azul

VERSION

    $Id$
"""
 
import sys, os, time, atexit
from signal import SIGTERM
from datetime import datetime

class Daemon:
	"""
	A generic daemon class.

	Usage: subclass the Daemon class and override the run() method
	"""
	def __init__(self, pidfile, stdin='/dev/null', stdout='/dev/null', stderr='/dev/null'):
		self.stdin = stdin
		self.stdout = '/var/www/bolas_sag/logs/webSocketlog.out' #stdout
		self.stderr = '/var/www/bolas_sag/logs/webSocketlog.err' #stderr
		self.pidfile = pidfile

	def daemonize(self):
		"""
		do the UNIX double-fork magic, see Stevens' "Advanced
		Programming in the UNIX Environment" for details (ISBN 0201563177)
		http://www.erlenstar.demon.co.uk/unix/faq_2.html#SEC16
		"""
		try:
			pid = os.fork()
			if pid > 0:
				# exit first parent
				sys.exit(0)
		except OSError, e:
			sys.stderr.write("fork #1 failed: %d (%s)\n" % (e.errno, e.strerror))
			sys.exit(1)

		# decouple from parent environment
		os.chdir("/")
		os.setsid()
		os.umask(0)

		# do second fork
		try:
			pid = os.fork()
			if pid > 0:
				# exit from second parent
				sys.exit(0)
		except OSError, e:
			sys.stderr.write("fork #2 failed: %d (%s)\n" % (e.errno, e.strerror))
			sys.exit(1)


		try:
			# redirect standard file descriptors
			sys.stdout.flush()
			sys.stderr.flush()
			si = file(self.stdin, 'r')
			so = file(self.stdout, 'a+', 128)
			se = file(self.stderr, 'a+', 0)
			os.dup2(si.fileno(), sys.stdin.fileno())
			os.dup2(so.fileno(), sys.stdout.fileno())
			os.dup2(se.fileno(), sys.stderr.fileno())
		except Exception as e:
			sys.stderr.write("\n error({0}): {1}".format(e.errno, e.strerror) + "\n")
		
		

		# write pidfile
		atexit.register(self.delpid)
		pid = str(os.getpid())
		file(self.pidfile,'w+').write("%s\n" % pid)

	def delpid(self):
		os.remove(self.pidfile)

	def start(self):
		"""
		Start the daemon
		"""
		
		# Check for a pidfile to see if the daemon already runs
		try:
			pf = file(self.pidfile,'r')
			pid = int(pf.read().strip())
			pf.close()
		except IOError:
			pid = None

		if pid:
			message = "pidfile %s already exist. Daemon already running?\n"
			sys.stderr.write(message % self.pidfile)
			sys.exit(1)

		# Start the daemon
		self.daemonize()
		self.run()

	def stop(self):
		"""
		Stop the daemon
		"""
		# Get the pid from the pidfile
		try:
			pf = file(self.pidfile,'r')
			pid = int(pf.read().strip())
			pf.close()
		except IOError:
			pid = None

		if not pid:
			message = "pidfile %s does not exist. Daemon not running?\n"
			sys.stderr.write(message % self.pidfile)
			return # not an error in a restart

		 # Try killing the daemon process       
		try:
			while 1:
				os.kill(pid, SIGTERM)
				time.sleep(0.1)
		except OSError, err:
			err = str(err)
			if err.find("No such process") > 0:
				if os.path.exists(self.pidfile):
					os.remove(self.pidfile)
			else:
				print str(err)
				sys.exit(1)

	def restart(self):
		"""
		Restart the daemon
		"""
		self.stop()
		self.start()	


	def reload(self):
		"""	
		Reload the daemon's parameters
		"""
		#sys.stderr.write('reload')
		#self.reload()


	def run(self):
		"""
		You should override this method when you subclass Daemon. It will be called after the process has been
		daemonized by start() or restart().
		"""
