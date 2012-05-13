package com.shdchi;

import gnu.io.CommPort;
import gnu.io.CommPortIdentifier;
import gnu.io.SerialPort;

import java.io.BufferedReader;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.util.Enumeration;

import com.shdchi.commands.CarCommand;
import com.shdchi.file.FileReader;

public class RemoteCommander
{
	
	public static final String DEFUALT_TTY_PORT = "/dev/tty.usbmodemfa136";
	public static final String DEFAULT_TEST_FILE = "/assets/test_file.txt";
	public CommPort commPort = null; 
	public Thread inputThread = null;
	public Thread outputThread = null;
	public SerialWriter serialWriter = null;
	
    public RemoteCommander()
    {
        super();
    }
    
    private OutputStream getOutputStream(String fileName) throws Exception {
    	
        CommPortIdentifier portIdentifier = CommPortIdentifier.getPortIdentifier(getPortName());
        OutputStream outputStream = null;
        
        if ( portIdentifier.isCurrentlyOwned() ) {
            System.out.println("Error: Port is currently in use");
        } else {
            this.commPort = portIdentifier.open("remote_control",2000);
            if ( commPort instanceof SerialPort ) {
                SerialPort serialPort = (SerialPort) commPort;
                serialPort.setSerialPortParams(115200,SerialPort.DATABITS_8,SerialPort.STOPBITS_1,SerialPort.PARITY_NONE);
                
                Thread.sleep(4000);
                
                //InputStream in = serialPort.getInputStream();
                //inputThread = new Thread(new SerialReader(in));
                //inputThread.start();
                
                outputStream = serialPort.getOutputStream();
                serialWriter = new SerialWriter(fileName, outputStream, this);
                outputThread = new Thread(serialWriter);
                outputThread.start();
                
            } else {
                System.out.println("Error: Only serial ports are handled by this example.");
            }
        }
        
        return outputStream;
    }
    
    private String getPortName() {
    	String portName = null;
		@SuppressWarnings("rawtypes")
		Enumeration ports = CommPortIdentifier.getPortIdentifiers();  
        while(ports.hasMoreElements()){  
            CommPortIdentifier port = (CommPortIdentifier) ports.nextElement();
            if (port.getName().contains("tty.usbmodem")) {
            	portName = port.getName();
            	System.out.println("Found port: " + portName);
            	return portName;
            }
        }
        
        if (portName == null) {
        	portName = DEFUALT_TTY_PORT;
        	System.out.println("Using default port: " + portName);
        }
        
		return portName;

    }
    
    public static class SerialReader implements Runnable 
    {
        InputStream in;
        
        public SerialReader ( InputStream in )
        {
            this.in = in;
        }
        
        public void run ()
        {
            byte[] buffer = new byte[1024];
            int len = -1;
            try
            {
            	System.out.println("RECEIVED:");
                while ( ( len = this.in.read(buffer)) > -1 )
                {
                    System.out.print(new String(buffer,0,len));
                }
            }
            catch ( IOException e )
            {
                e.printStackTrace();
                return;
            }            
        }   
    }
    
    public static class SerialWriter implements Runnable 
    {
        OutputStream out;
        String fileName;
        RemoteCommander commander = null;
        
        public SerialWriter (String fileName, OutputStream out, RemoteCommander commander )
        {
            this.out = out;
            this.fileName = fileName;
            this.commander = commander;
        }
        
        public void run ()
        {
            this.commander.writeFileCommandsToStream(this.fileName, this.out);
            System.exit(0);
        }
       
    }
    
    private void writeFileCommandsToStream(String fileName, OutputStream out) {
    	BufferedReader fileInput = null;
    	
    	// Get the file input stream
    	try {
    		fileInput = FileReader.getReaderFromFile(fileName);
    		System.out.println("Using the given file: " + fileName);
    	} catch (FileNotFoundException fnfe) {
    		System.out.println("Could not open the given file.");
    		// Try getting the test file...
    		try {
    			fileInput = FileReader.getReaderFromFile(this.getClass().getResource(DEFAULT_TEST_FILE).getPath());
    			System.out.println("Using the test file: " + DEFAULT_TEST_FILE);
    		} catch (FileNotFoundException anotherFileNotFoundException) {
    			// We have no way to do anything.
    			System.out.println("Could not open the test file.");
    			return;
    		}
    	}
    	
    	if ( fileInput != null ) {
    		
    		String fileLine = null;
    		int lineNumber = 1;
    		
    		try {
    			
    			// Write START
    			System.out.println("SENDING: START FROM THREAD");
        		out.write(CarCommand.START);
    			
				while ( (fileLine = fileInput.readLine()) != null ) {
					
					// Check to see if the line is not empty
					if ( fileLine == null || fileLine.isEmpty() ) {
						System.out.println("Error: Line number " + lineNumber + " is empty.");
						lineNumber++;
						break;
					}
					
					// For each line, create a command
					// Parse the line
					String commandName = null;
					String commandValue = null;
					
					String[] lineStringArray = fileLine.split("\\s");
					
					if ( lineStringArray.length != 2 ) {
						System.out.println("Error: Line number " + lineNumber + " does not contain both arguments, expected:");
						System.out.println("<command> <value>");
						System.out.println("ex: FORWARD 10");
						lineNumber++;
						break;
					}
					
					commandName = lineStringArray[0];
					commandValue = lineStringArray[1];
					
					if ( commandName == null || commandName.isEmpty() ) {
						System.out.println("Error: Line number " + lineNumber + " does not contain a valid command.");
						lineNumber++;
						break;
					}
					
					if ( commandValue == null || commandName.isEmpty() ) {
						System.out.println("Error: Line number " + lineNumber + " does not contain a valid command value.");
						lineNumber++;
						break;
					}
					
					System.out.println("SENDING: " + commandName + " " + commandValue);
					byte[] command = CarCommand.getCommand(commandName, commandValue);
					if (command != null) {
						System.out.println("SENDING RAW: " + command[0] + " " + command[1]);
						out.write(command);
						out.flush();
					} else {
						System.out.println("Unable to parse value at line " + lineNumber);
					}
					
					lineNumber++;
				}
			} catch (IOException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			} finally {
				// Write STOP
	    		try {
	    			System.out.println("SENDING: STOP");
					out.write(CarCommand.STOP);
					out.flush();
					out.close();	
				} catch (IOException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
			}
    		
    	} else {
    		System.out.println("Unable to read from the given file.");
    	}
    }
    
    public static void main ( String[] args )
    {
    	
    	RemoteCommander remoteCommander = new RemoteCommander();
    	
    	String fileName = null;
    	
    	// Get the params
    	if ( args.length >= 1 ) {
    		// We have both a file and port name, and maybe others
    		fileName = args[0];

    	} else if (args.length == 0) {
    		// We have no arguments
    		System.out.println("No arguments passed, using defaults.");
    	}
    	
    	try {
    		remoteCommander.getOutputStream(fileName);
    	} catch ( Exception e ) {
    		System.out.println("Could not open the serial output stream.");
            e.printStackTrace();
            System.exit(0);
        }
    	
    }
}