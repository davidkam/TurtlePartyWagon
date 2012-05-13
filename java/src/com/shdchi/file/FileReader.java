package com.shdchi.file;

import java.io.BufferedReader;
import java.io.DataInputStream;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.InputStreamReader;

public class FileReader {

	public static BufferedReader getReaderFromFile(String fileName) throws FileNotFoundException {
		if ( fileName == null ) {
			throw new FileNotFoundException();
		}
		
		BufferedReader br = null;
		FileInputStream fstream = new FileInputStream(fileName);
		// Get the object of DataInputStream
		DataInputStream in = new DataInputStream(fstream);
		br = new BufferedReader(new InputStreamReader(in));
		return br;
	}

}
